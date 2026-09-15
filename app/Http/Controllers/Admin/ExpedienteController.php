<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\ConsultaPhoto;
use App\Models\Doctor;
use App\Models\Expediente;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Patient;
use App\Models\Receta;
use App\Models\Service;
use App\Models\Speciality;
use App\Models\ToothTreatment;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Historial clínico por especialidad: cada Expediente agrupa, para un
 * paciente y una especialidad, todas sus Consultas (visitas) a lo largo
 * del tiempo. Reemplaza a HistoryController para el uso diario; ese
 * controlador se deja intacto como archivo de solo lectura de los
 * historiales que existían antes de este cambio.
 */
class ExpedienteController extends Controller
{
    public function __construct()
    {
        // Reutilizamos las mismas habilidades de "Historial Médico": es la
        // misma área funcional, solo cambia cómo se organiza la información.
        $this->middleware('can:admin.histories.index')->only('index');
        $this->middleware('can:admin.histories.show')->only('show', 'pdfConsulta', 'recetasHistorial', 'pdfReceta', 'odontograma', 'odontogramaPdf');
        $this->middleware('can:admin.histories.create')->only('create', 'store', 'storeConsulta', 'editConsulta', 'updateConsulta');
        $this->middleware('can:admin.histories.addNote')->only('addNote');
        $this->middleware('can:admin.histories.photos.store')->only('storePhoto');
        $this->middleware('can:admin.histories.photos.destroy')->only('destroyPhoto');
        $this->middleware('can:admin.histories.recetas.store')->only('storeReceta', 'updateReceta');
        $this->middleware('can:admin.histories.recetas.destroy')->only('destroyReceta');
        $this->middleware('can:admin.histories.odontograma.store')->only('storeToothTreatment', 'updateToothTreatment');
        $this->middleware('can:admin.histories.odontograma.destroy')->only('destroyToothTreatment');
    }

    public function index()
    {
        // El listado y la búsqueda en tiempo real los maneja el componente
        // Livewire admin.expediente-search (ver app/Livewire/Admin/ExpedienteSearch.php).
        return view('admin.expedientes.index');
    }

    /**
     * Formulario para abrir un expediente nuevo (paciente + especialidad)
     * sin que tenga que existir todavía una venta o una consulta previa:
     * por ejemplo, para dejarlo listo antes de la primera visita.
     */
    public function create()
    {
        $patients = Patient::with('person')->where('status', 1)->orderBy('id', 'desc')->get();
        $specialities = Speciality::where('status', 1)->get();

        return view('admin.expedientes.create', compact('patients', 'specialities'));
    }

    /**
     * Crea el expediente (o, si ya existía para ese paciente y especialidad,
     * simplemente lleva al que ya existe, sin duplicar nada).
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'speciality_id' => 'required|exists:specialities,id',
        ], [
            'patient_id.required' => 'Debe seleccionar un paciente.',
            'patient_id.exists' => 'El paciente seleccionado no es válido.',
            'speciality_id.required' => 'Debe seleccionar una especialidad.',
            'speciality_id.exists' => 'La especialidad seleccionada no es válida.',
        ]);

        // Si un doctor con cuenta vinculada abre el expediente, solo puede
        // hacerlo en una especialidad a la que tenga acceso. Admin siempre
        // tiene acceso a todo, sin importar si además está vinculado a
        // algún doctor (ver authorizeAccess más abajo para la explicación).
        $doctor = auth()->user()?->doctor;

        if ($doctor && ! auth()->user()->hasRole('Admin')) {
            abort_unless(
                $doctor->canViewSpeciality((int) $request->speciality_id),
                403,
                'No tienes acceso a esa especialidad.'
            );
        }

        $expediente = Expediente::firstOrCreate(
            ['patient_id' => $request->patient_id, 'speciality_id' => $request->speciality_id],
            ['status' => true]
        );

        session()->flash('swal', $expediente->wasRecentlyCreated
            ? ['title' => 'Expediente creado', 'text' => '¡Bien Hecho!.', 'icon' => 'success']
            : ['title' => 'Ya existía', 'text' => 'Ese paciente ya tenía un expediente en esa especialidad, te llevamos a él.', 'icon' => 'info']
        );

        return redirect()->route('admin.expedientes.show', $expediente);
    }

    public function show(Expediente $expediente)
    {
        $this->authorizeAccess($expediente);

        $expediente->load([
            'patient.person',
            'speciality',
            'consultas' => fn ($query) => $query->orderByDesc('date')->orderByDesc('id'),
            'consultas.doctor.person',
            'consultas.notes',
            'consultas.photos',
            'consultas.recetas',
            // Cada consulta recuerda con qué plantilla se llenó, para poder
            // mostrar sus respuestas (Consulta::data) aunque la plantilla
            // cambie más adelante.
            'consultas.formTemplate.sections.fields',
        ]);

        // Si quien entró es un doctor con cuenta vinculada, la consulta se
        // registra siempre a su propio nombre: no tiene sentido pedirle que
        // "elija" quién la atendió. Solo Admin/Recepción (sin doctor propio)
        // necesitan elegir entre los doctores de la especialidad.
        $doctorActual = auth()->user()?->doctor;

        $doctoresDeEspecialidad = $doctorActual
            ? collect()
            : Doctor::where('speciality_id', $expediente->speciality_id)
                ->where('status', 1)
                ->with('person')
                ->get();

        // Plantilla(s) activa(s) de la especialidad: si existe alguna, el
        // formulario de "Registrar consulta" muestra sus secciones y campos
        // debajo de los datos fijos (doctor, fecha, descripción). Puede
        // haber más de una (ej. Medicina Ortomolecular: "Historia Clínica
        // Funcional" + "Perfil Neurofuncional"); en ese caso se elige cuál
        // completar al registrar la consulta.
        $activeTemplates = $expediente->speciality
            ?->activeFormTemplates()
            ->with('sections.fields')
            ->get() ?? collect();

        return view('admin.expedientes.show', compact('expediente', 'doctoresDeEspecialidad', 'doctorActual', 'activeTemplates'));
    }

    /**
     * Registra una consulta (visita) nueva dentro de un expediente, de forma
     * manual (sin pasar por una venta).
     */
    public function storeConsulta(Request $request, Expediente $expediente)
    {
        $this->authorizeAccess($expediente);

        // Si el que registra es un doctor con cuenta vinculada, la consulta
        // queda a su propio nombre siempre: no se le pide (ni se le permite)
        // elegir otro doctor del listado.
        $doctorActual = auth()->user()?->doctor;

        $rules = [
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
        ];

        if (! $doctorActual) {
            $rules['doctor_id'] = 'required|exists:doctors,id';
        }

        $request->validate($rules, [
            'doctor_id.required' => 'Debe seleccionar quién atendió la consulta.',
            'doctor_id.exists' => 'El doctor seleccionado no es válido.',
            'date.required' => 'Debe indicar la fecha de la consulta.',
            'date.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        if ($doctorActual) {
            $doctor = $doctorActual;
        } else {
            $doctor = Doctor::findOrFail($request->doctor_id);

            abort_unless(
                (int) $doctor->speciality_id === (int) $expediente->speciality_id,
                422,
                'Ese doctor no pertenece a la especialidad de este expediente.'
            );
        }

        // Si la especialidad tiene una (o más) plantilla(s) activa(s),
        // debajo de los datos fijos se muestran sus campos (ver la vista);
        // acá se validan y se arma el JSON que se guarda en Consulta::data.
        // Se guarda además "form_template_id" para que, aunque la plantilla
        // cambie después, esta consulta se siga mostrando con los campos
        // que tenía en su momento.
        $activeTemplates = $expediente->speciality
            ?->activeFormTemplates()
            ->with('sections.fields')
            ->get() ?? collect();

        if ($activeTemplates->count() > 1) {
            $request->validate([
                'form_template_id' => 'required|exists:form_templates,id',
            ], [
                'form_template_id.required' => 'Debe elegir qué formulario va a completar.',
            ]);

            $activeTemplate = $activeTemplates->firstWhere('id', (int) $request->form_template_id);

            abort_unless($activeTemplate, 422, 'Ese formulario no pertenece a esta especialidad.');
        } else {
            $activeTemplate = $activeTemplates->first();
        }

        $data = $activeTemplate ? $this->collectFormData($request, $activeTemplate) : null;

        Consulta::create([
            'expediente_id' => $expediente->id,
            'doctor_id' => $doctor->id,
            'form_template_id' => $activeTemplate?->id,
            'description' => $request->description,
            'date' => $request->date,
            'data' => $data,
        ]);

        session()->flash('swal', [
            'title' => 'Consulta registrada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $expediente);
    }

    /**
     * Imprime (PDF) una consulta puntual: datos del paciente, del doctor y
     * todo lo que se completó del formulario de la especialidad en esa
     * visita. Usa la plantilla que la consulta tiene guardada
     * (form_template_id), no la plantilla activa actual, para que el
     * impreso siempre coincida con lo que realmente se cargó ese día.
     */
    public function pdfConsulta(Consulta $consulta)
    {
        $this->authorizeAccess($consulta->expediente);

        $consulta->load([
            'expediente.patient.person',
            'expediente.speciality',
            'doctor.person',
            'formTemplate.sections.fields',
            'notes',
        ]);

        $pdf = PDF::loadView('admin.expedientes.consulta_pdf', compact('consulta'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('consulta_' . $consulta->id . '.pdf');
    }

    /**
     * Formulario para corregir una consulta ya guardada: por ejemplo, si se
     * envió "Registrar consulta" sin querer antes de terminar de llenarla.
     * Se edita con la plantilla que la consulta tiene guardada
     * (form_template_id), no la que esté activa hoy, para no mezclar
     * campos de una versión distinta con respuestas ya guardadas.
     */
    public function editConsulta(Consulta $consulta)
    {
        $this->authorizeAccess($consulta->expediente);

        $consulta->load(['expediente.speciality', 'doctor.person', 'formTemplate.sections.fields']);

        $doctorActual = auth()->user()?->doctor;

        $doctoresDeEspecialidad = $doctorActual
            ? collect()
            : Doctor::where('speciality_id', $consulta->expediente->speciality_id)
                ->where('status', 1)
                ->with('person')
                ->get();

        $template = $consulta->formTemplate;

        return view('admin.expedientes.consulta_edit', compact('consulta', 'doctoresDeEspecialidad', 'doctorActual', 'template'));
    }

    /**
     * Guarda las correcciones de una consulta existente. Sigue las mismas
     * reglas que al crearla (storeConsulta): el doctor logueado no elige,
     * queda con su propio nombre; Admin/Recepción sí eligen de la lista.
     */
    public function updateConsulta(Request $request, Consulta $consulta)
    {
        $this->authorizeAccess($consulta->expediente);

        $doctorActual = auth()->user()?->doctor;

        $rules = [
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
        ];

        if (! $doctorActual) {
            $rules['doctor_id'] = 'required|exists:doctors,id';
        }

        $request->validate($rules, [
            'doctor_id.required' => 'Debe seleccionar quién atendió la consulta.',
            'doctor_id.exists' => 'El doctor seleccionado no es válido.',
            'date.required' => 'Debe indicar la fecha de la consulta.',
            'date.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        if ($doctorActual) {
            $doctor = $doctorActual;
        } else {
            $doctor = Doctor::findOrFail($request->doctor_id);

            abort_unless(
                (int) $doctor->speciality_id === (int) $consulta->expediente->speciality_id,
                422,
                'Ese doctor no pertenece a la especialidad de este expediente.'
            );
        }

        // Se sigue completando la misma plantilla con la que se creó la
        // consulta (form_template_id no cambia acá), aunque hoy exista una
        // versión más nueva activa para la especialidad.
        $template = $consulta->formTemplate;
        $data = $template ? $this->collectFormData($request, $template) : null;

        $consulta->update([
            'doctor_id' => $doctor->id,
            'description' => $request->description,
            'date' => $request->date,
            'data' => $data,
        ]);

        session()->flash('swal', [
            'title' => 'Consulta actualizada',
            'text' => '¡Bien hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $consulta->expediente_id);
    }

    public function addNote(Request $request, Consulta $consulta)
    {
        $this->authorizeAccess($consulta->expediente);

        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $consulta->notes()->create([
            'note' => $request->note,
        ]);

        session()->flash('swal', [
            'title' => 'Nota agregada',
            'text' => 'Se ha registrado una nueva nota.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $consulta->expediente_id);
    }

    /**
     * Sube una o varias fotos (Antes/Después) para una consulta.
     */
    public function storePhoto(Request $request, Consulta $consulta)
    {
        $this->authorizeAccess($consulta->expediente);

        $request->validate([
            'type' => 'required|in:antes,despues',
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'type.required' => 'Debe indicar si la foto es "Antes" o "Después".',
            'photos.required' => 'Debe seleccionar al menos una foto.',
            'photos.*.image' => 'El archivo debe ser una imagen.',
            'photos.*.mimes' => 'Formatos permitidos: JPG, PNG o WEBP.',
            'photos.*.max' => 'Cada foto no debe superar los 5MB.',
        ]);

        foreach ($request->file('photos') as $file) {
            $path = $file->store('consultas/' . $consulta->id, 'public');

            $consulta->photos()->create([
                'type' => $request->type,
                'path' => $path,
            ]);
        }

        session()->flash('swal', [
            'title' => 'Fotos agregadas',
            'text' => 'Se guardaron las fotos correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $consulta->expediente_id);
    }

    /**
     * Elimina una foto (Antes/Después) de una consulta.
     */
    public function destroyPhoto(ConsultaPhoto $photo)
    {
        $expedienteId = $photo->consulta->expediente_id;

        $this->authorizeAccess($photo->consulta->expediente);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        session()->flash('swal', [
            'title' => 'Foto eliminada',
            'text' => 'Se eliminó la foto correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $expedienteId);
    }

    /**
     * Escribe una receta médica nueva para una consulta. El contenido es
     * texto libre: el doctor la redacta como la escribiría a mano
     * (medicamento, dosis, frecuencia, indicaciones...).
     */
    public function storeReceta(Request $request, Consulta $consulta)
    {
        $this->authorizeAccess($consulta->expediente);

        $request->validate([
            'contenido' => 'required|string|max:4000',
        ], [
            'contenido.required' => 'Debe escribir el contenido de la receta.',
        ]);

        $consulta->recetas()->create([
            'contenido' => $request->contenido,
        ]);

        session()->flash('swal', [
            'title' => 'Receta guardada',
            'text' => 'Se ha registrado una nueva receta.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $consulta->expediente_id);
    }

    /**
     * Corrige una receta ya guardada (por ejemplo, un error de tipeo).
     */
    public function updateReceta(Request $request, Receta $receta)
    {
        $this->authorizeAccess($receta->consulta->expediente);

        $request->validate([
            'contenido' => 'required|string|max:4000',
        ], [
            'contenido.required' => 'Debe escribir el contenido de la receta.',
        ]);

        $receta->update([
            'contenido' => $request->contenido,
        ]);

        session()->flash('swal', [
            'title' => 'Receta actualizada',
            'text' => 'Se guardaron los cambios de la receta.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $receta->consulta->expediente_id);
    }

    /**
     * Elimina una receta (por ejemplo, si se cargó por error).
     */
    public function destroyReceta(Receta $receta)
    {
        $expedienteId = $receta->consulta->expediente_id;

        $this->authorizeAccess($receta->consulta->expediente);

        $receta->delete();

        session()->flash('swal', [
            'title' => 'Receta eliminada',
            'text' => 'Se eliminó la receta correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.show', $expedienteId);
    }

    /**
     * Imprime (PDF) una receta puntual, con formato de receta médica lista
     * para entregar al paciente.
     */
    public function pdfReceta(Receta $receta)
    {
        $this->authorizeAccess($receta->consulta->expediente);

        $receta->load([
            'consulta.expediente.patient.person',
            'consulta.expediente.speciality',
            'consulta.doctor.person',
        ]);

        $pdf = PDF::loadView('admin.expedientes.receta_pdf', compact('receta'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('receta_' . $receta->id . '.pdf');
    }

    /**
     * Historial de todas las recetas de un expediente (todas las consultas
     * de ese paciente en esa especialidad), de la más reciente a la más
     * antigua. Responde a "¿qué recetas ya le di a este paciente?".
     */
    public function recetasHistorial(Expediente $expediente)
    {
        $this->authorizeAccess($expediente);

        $expediente->load([
            'patient.person',
            'speciality',
        ]);

        $recetas = Receta::whereHas('consulta', function ($query) use ($expediente) {
                $query->where('expediente_id', $expediente->id);
            })
            ->with('consulta.doctor.person')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.expedientes.recetas_historial', compact('expediente', 'recetas'));
    }

    /**
     * Odontograma de un expediente: el gráfico de piezas dentales (clic en
     * un diente para cargarle un tratamiento) más el listado de todos los
     * tratamientos ya registrados, de más nuevo a más viejo. Los
     * tratamientos van contra el Expediente, no contra una Consulta
     * puntual, porque el odontograma es el estado de toda la boca a lo
     * largo del tiempo.
     */
    public function odontograma(Expediente $expediente)
    {
        $this->authorizeAccess($expediente);
        $this->authorizeOdontograma($expediente);

        $expediente->load(['patient.person', 'speciality']);

        $treatments = $expediente->toothTreatments()->with('sale')->get();

        // Última entrada de cada pieza (ya viene ordenado de más nuevo a
        // más viejo), para pintar el gráfico según si está realizado,
        // pendiente, o todavía sin registrar.
        $ultimaPorDiente = $treatments->groupBy('tooth_number')->map->first();

        // Tratamientos que todavía no se cobraron: el botón "Cobrar
        // pendientes" los manda juntos a Ventas, con el paciente y el
        // total ya armados (ver SaleController::create/store).
        $pendientes = $treatments->whereNull('sale_id');

        // Para elegir el diagnóstico/tratamiento de la lista de Servicios
        // (con su precio) en vez de tener que escribirlo siempre a mano.
        // Sigue siendo editable: si no está en el catálogo, se escribe igual.
        $services = Service::where('status', 1)->orderBy('name')->get();

        return view('admin.expedientes.odontograma', compact('expediente', 'treatments', 'ultimaPorDiente', 'pendientes', 'services'));
    }

    /**
     * PDF del plan de tratamiento del odontograma, para entregarle al
     * paciente: qué se le diagnosticó/recomendó en cada pieza, qué ya se
     * hizo y el total (con el desglose de lo ya cobrado y lo pendiente).
     */
    public function odontogramaPdf(Expediente $expediente)
    {
        $this->authorizeAccess($expediente);
        $this->authorizeOdontograma($expediente);

        $expediente->load(['patient.person', 'speciality']);

        $treatments = $expediente->toothTreatments()->with('sale')->get();

        // Mismo criterio que en la pantalla del odontograma: "pendiente de
        // cobro" es que todavía no esté ligado a ninguna venta.
        $totalPendienteCobro = $treatments->whereNull('sale_id')->sum(fn ($t) => (float) ($t->price ?? 0));
        $totalRegistrado = $treatments->sum(fn ($t) => (float) ($t->price ?? 0));
        $totalCobrado = $totalRegistrado - $totalPendienteCobro;

        // El odontograma no liga cada tratamiento a un doctor puntual (no
        // depende de una Consulta). Si quien exporta es un doctor, se
        // muestra a él; si no (recepción/admin), se usa el doctor de la
        // consulta más reciente del expediente, si existe alguna.
        $doctor = auth()->user()?->doctor
            ?? optional($expediente->consultas()->with('doctor.person')->first())->doctor;

        $pdf = PDF::loadView('admin.expedientes.odontograma_pdf', compact(
            'expediente',
            'treatments',
            'totalRegistrado',
            'totalCobrado',
            'totalPendienteCobro',
            'doctor'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('plan_tratamiento_' . $expediente->id . '.pdf');
    }

    /**
     * Carga un tratamiento nuevo sobre una pieza dental del odontograma.
     */
    public function storeToothTreatment(Request $request, Expediente $expediente)
    {
        $this->authorizeAccess($expediente);
        $this->authorizeOdontograma($expediente);

        $request->validate([
            'tooth_number' => 'required|string|max:5',
            'treatment' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'date' => 'nullable|date|before_or_equal:today',
        ], [
            'tooth_number.required' => 'Debe seleccionar una pieza dental.',
            'treatment.required' => 'Debe indicar el diagnóstico o tratamiento.',
            'price.numeric' => 'El precio debe ser un número.',
            'date.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        $expediente->toothTreatments()->create([
            'tooth_number' => $request->tooth_number,
            'treatment' => $request->treatment,
            'price' => $request->price,
            'completed' => $request->boolean('completed'),
            'date' => $request->date ?: now()->format('Y-m-d'),
        ]);

        session()->flash('swal', [
            'title' => 'Tratamiento registrado',
            'text' => 'Se agregó al odontograma.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.odontograma.index', $expediente);
    }

    /**
     * Corrige un tratamiento del odontograma ya guardado.
     */
    public function updateToothTreatment(Request $request, ToothTreatment $toothTreatment)
    {
        $this->authorizeAccess($toothTreatment->expediente);
        $this->authorizeOdontograma($toothTreatment->expediente);

        $request->validate([
            'tooth_number' => 'required|string|max:5',
            'treatment' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'date' => 'nullable|date|before_or_equal:today',
        ], [
            'tooth_number.required' => 'Debe seleccionar una pieza dental.',
            'treatment.required' => 'Debe indicar el diagnóstico o tratamiento.',
            'price.numeric' => 'El precio debe ser un número.',
            'date.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        $toothTreatment->update([
            'tooth_number' => $request->tooth_number,
            'treatment' => $request->treatment,
            'price' => $request->price,
            'completed' => $request->boolean('completed'),
            'date' => $request->date ?: $toothTreatment->date,
        ]);

        session()->flash('swal', [
            'title' => 'Tratamiento actualizado',
            'text' => 'Se guardaron los cambios.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.odontograma.index', $toothTreatment->expediente_id);
    }

    /**
     * Elimina un tratamiento del odontograma (por ejemplo, si se cargó por error).
     */
    public function destroyToothTreatment(ToothTreatment $toothTreatment)
    {
        $expedienteId = $toothTreatment->expediente_id;

        $this->authorizeAccess($toothTreatment->expediente);
        $this->authorizeOdontograma($toothTreatment->expediente);

        $toothTreatment->delete();

        session()->flash('swal', [
            'title' => 'Tratamiento eliminado',
            'text' => 'Se eliminó el tratamiento correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.expedientes.odontograma.index', $expedienteId);
    }

    /**
     * Valida y arma, a partir de los campos de la plantilla activa, el
     * array que se guarda como Consulta::data (clave = form_field_id).
     * Tira un ValidationException si falta algún campo obligatorio, igual
     * que $request->validate() en el resto del controlador.
     */
    private function collectFormData(Request $request, FormTemplate $template): array
    {
        $data = [];

        foreach ($template->sections as $section) {
            foreach ($section->fields as $field) {
                if ($field->type === FormField::TYPE_TABLA_REPETIBLE) {
                    $filas = collect($request->input("data.{$field->id}", []))
                        ->map(fn ($fila) => array_map('trim', (array) $fila))
                        ->filter(fn ($fila) => collect($fila)->filter(fn ($valor) => $valor !== '')->isNotEmpty())
                        ->values()
                        ->all();

                    if ($field->required && empty($filas)) {
                        throw ValidationException::withMessages([
                            "data.{$field->id}" => "El campo \"{$field->label}\" es obligatorio.",
                        ]);
                    }

                    if (! empty($filas)) {
                        $data[$field->id] = $filas;
                    }

                    continue;
                }

                $valor = $request->input("data.{$field->id}");
                $estaVacio = $valor === null || $valor === '' || (is_array($valor) && empty($valor));

                if ($field->required && $estaVacio) {
                    throw ValidationException::withMessages([
                        "data.{$field->id}" => "El campo \"{$field->label}\" es obligatorio.",
                    ]);
                }

                if (! $estaVacio) {
                    $data[$field->id] = $valor;
                }
            }
        }

        return $data;
    }

    /**
     * Verifica que el médico autenticado (si lo es) tenga acceso a la
     * especialidad de este expediente. Un médico solo ve por defecto los
     * expedientes de su propia especialidad, salvo que se le haya
     * autorizado explícitamente otra desde la ficha del doctor.
     *
     * Los usuarios que no son un Doctor vinculado (Admin, Recepción, etc.)
     * no tienen esta restricción. Además, el rol Admin siempre tiene acceso
     * a todo el sistema por defecto, aunque su cuenta esté vinculada a un
     * doctor (no debería pasar, pero así queda garantizado igual).
     */
    private function authorizeAccess(Expediente $expediente): void
    {
        if (auth()->user()?->hasRole('Admin')) {
            return;
        }

        $doctor = auth()->user()?->doctor;

        if (! $doctor) {
            return;
        }

        abort_unless(
            $doctor->canViewSpeciality($expediente->speciality_id),
            403,
            'No tienes acceso al expediente clínico de esta especialidad.'
        );
    }

    /**
     * El odontograma es específico de Odontología: no tiene sentido para
     * el resto de las especialidades (Medicina Ortomolecular, Nutrición,
     * etc.), así que se bloquea acá además de ocultar el botón en la
     * vista (ver admin.expedientes.show), por si alguien entra por URL
     * directa.
     */
    private function authorizeOdontograma(Expediente $expediente): void
    {
        abort_unless(
            $expediente->speciality?->name === 'Odontología General',
            403,
            'El odontograma solo está disponible para Odontología.'
        );
    }
}
