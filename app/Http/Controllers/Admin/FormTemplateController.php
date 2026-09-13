<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Constructor de formularios sin código: acá se arma, por especialidad, qué
 * secciones y campos se llenan al registrar una Consulta (ver
 * ExpedienteController::storeConsulta). Cada especialidad tiene, por ahora,
 * una única plantilla activa (sin manejo de versiones desde esta pantalla
 * todavía): editar sus secciones/campos cambia cómo se ve el formulario
 * para las consultas nuevas, pero las consultas ya guardadas no se alteran
 * porque cada una recuerda su propio "form_template_id" y su "data".
 */
class FormTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.form_templates.edit');
    }

    /**
     * Pantalla del constructor para una especialidad: si todavía no tiene
     * plantilla, se ofrece crear la primera; si ya la tiene, se listan sus
     * secciones y campos para editarlos.
     */
    public function edit(Speciality $speciality)
    {
        $template = $speciality->activeFormTemplate()
            ->with('sections.fields')
            ->first();

        return view('admin.form_templates.edit', compact('speciality', 'template'));
    }

    /**
     * Crea la primera (o siguiente) versión de la plantilla de una
     * especialidad. Solo puede haber una activa a la vez.
     */
    public function store(Request $request, Speciality $speciality)
    {
        abort_if(
            $speciality->activeFormTemplate()->exists(),
            422,
            'Esta especialidad ya tiene una plantilla activa.'
        );

        $request->validate([
            'name' => 'required|string|max:150',
        ], [
            'name.required' => 'Debe indicar un nombre para la plantilla.',
        ]);

        $nextVersion = ($speciality->formTemplates()->max('version') ?? 0) + 1;

        FormTemplate::create([
            'speciality_id' => $speciality->id,
            'name' => $request->name,
            'version' => $nextVersion,
            'status' => true,
        ]);

        session()->flash('swal', [
            'title' => 'Plantilla creada',
            'text' => 'Ahora podés agregar secciones y campos.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.specialities.plantilla.edit', $speciality);
    }

    /**
     * Agrega una sección nueva al final de la plantilla.
     */
    public function storeSection(Request $request, FormTemplate $formTemplate)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'grupo_visual' => 'nullable|string|max:150',
        ], [
            'title.required' => 'Debe indicar un título para la sección.',
        ]);

        $order = ($formTemplate->sections()->max('order') ?? 0) + 1;

        FormSection::create([
            'form_template_id' => $formTemplate->id,
            'title' => $request->title,
            'grupo_visual' => $request->grupo_visual,
            'order' => $order,
        ]);

        session()->flash('swal', [
            'title' => 'Sección agregada',
            'text' => '¡Bien hecho!.',
            'icon' => 'success',
        ]);

        return back();
    }

    public function updateSection(Request $request, FormSection $formSection)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'grupo_visual' => 'nullable|string|max:150',
        ], [
            'title.required' => 'Debe indicar un título para la sección.',
        ]);

        $formSection->update($request->only('title', 'grupo_visual'));

        session()->flash('swal', [
            'title' => 'Sección actualizada',
            'text' => '¡Bien hecho!.',
            'icon' => 'success',
        ]);

        return back();
    }

    /**
     * Borra la sección y, en cascada (a nivel de base de datos), todos sus
     * campos. No borra las respuestas ya guardadas en consultas anteriores:
     * esas viven como JSON dentro de cada Consulta, no dependen de que la
     * sección siga existiendo.
     */
    public function destroySection(FormSection $formSection)
    {
        $formSection->delete();

        session()->flash('swal', [
            'title' => 'Sección eliminada',
            'text' => '¡Bien hecho!.',
            'icon' => 'success',
        ]);

        return back();
    }

    /**
     * Sube o baja una sección un lugar, intercambiando su "order" con el de
     * la sección vecina. No hace falta reordenar todo lo demás.
     */
    public function moveSection(Request $request, FormSection $formSection)
    {
        $request->validate(['direction' => 'required|in:up,down']);

        $sibling = FormSection::where('form_template_id', $formSection->form_template_id)
            ->where('order', $request->direction === 'up' ? '<' : '>', $formSection->order)
            ->orderBy('order', $request->direction === 'up' ? 'desc' : 'asc')
            ->first();

        if ($sibling) {
            DB::transaction(function () use ($formSection, $sibling) {
                $ordenOriginal = $formSection->order;
                $formSection->update(['order' => $sibling->order]);
                $sibling->update(['order' => $ordenOriginal]);
            });
        }

        return back();
    }

    /**
     * Agrega un campo nuevo al final de una sección. Para los tipos que
     * necesitan opciones (selección única/múltiple, columnas de tabla), se
     * reciben como texto (una por línea) y se guardan como array JSON.
     */
    public function storeField(Request $request, FormSection $formSection)
    {
        $request->validate([
            'label' => 'required|string|max:200',
            'type' => 'required|in:' . implode(',', array_keys(FormField::TYPES)),
            'help_text' => 'nullable|string|max:255',
            'options' => 'nullable|string',
        ], [
            'label.required' => 'Debe indicar una etiqueta para el campo.',
            'type.required' => 'Debe elegir un tipo de campo.',
            'type.in' => 'El tipo de campo elegido no es válido.',
        ]);

        $options = $this->parseOptions($request->type, $request->options);

        if ($options === false) {
            return back()
                ->withErrors(['options' => 'Este tipo de campo necesita al menos una opción (una por línea).'])
                ->withInput();
        }

        $order = ($formSection->fields()->max('order') ?? 0) + 1;

        FormField::create([
            'form_section_id' => $formSection->id,
            'label' => $request->label,
            'type' => $request->type,
            'options' => $options,
            'required' => $request->boolean('required'),
            'help_text' => $request->help_text,
            'order' => $order,
        ]);

        session()->flash('swal', [
            'title' => 'Campo agregado',
            'text' => '¡Bien hecho!.',
            'icon' => 'success',
        ]);

        return back();
    }

    public function updateField(Request $request, FormField $formField)
    {
        $request->validate([
            'label' => 'required|string|max:200',
            'type' => 'required|in:' . implode(',', array_keys(FormField::TYPES)),
            'help_text' => 'nullable|string|max:255',
            'options' => 'nullable|string',
        ], [
            'label.required' => 'Debe indicar una etiqueta para el campo.',
            'type.required' => 'Debe elegir un tipo de campo.',
            'type.in' => 'El tipo de campo elegido no es válido.',
        ]);

        $options = $this->parseOptions($request->type, $request->options);

        if ($options === false) {
            return back()
                ->withErrors(['options' => 'Este tipo de campo necesita al menos una opción (una por línea).'])
                ->withInput();
        }

        $formField->update([
            'label' => $request->label,
            'type' => $request->type,
            'options' => $options,
            'required' => $request->boolean('required'),
            'help_text' => $request->help_text,
        ]);

        session()->flash('swal', [
            'title' => 'Campo actualizado',
            'text' => '¡Bien hecho!.',
            'icon' => 'success',
        ]);

        return back();
    }

    /**
     * Borra el campo. Igual que con las secciones: no toca las respuestas
     * que ya quedaron guardadas en consultas anteriores.
     */
    public function destroyField(FormField $formField)
    {
        $formField->delete();

        session()->flash('swal', [
            'title' => 'Campo eliminado',
            'text' => '¡Bien hecho!.',
            'icon' => 'success',
        ]);

        return back();
    }

    public function moveField(Request $request, FormField $formField)
    {
        $request->validate(['direction' => 'required|in:up,down']);

        $sibling = FormField::where('form_section_id', $formField->form_section_id)
            ->where('order', $request->direction === 'up' ? '<' : '>', $formField->order)
            ->orderBy('order', $request->direction === 'up' ? 'desc' : 'asc')
            ->first();

        if ($sibling) {
            DB::transaction(function () use ($formField, $sibling) {
                $ordenOriginal = $formField->order;
                $formField->update(['order' => $sibling->order]);
                $sibling->update(['order' => $ordenOriginal]);
            });
        }

        return back();
    }

    /**
     * Convierte el textarea de opciones (una por línea) en un array, solo
     * para los tipos que las necesitan. Devuelve `false` cuando el tipo las
     * necesita pero no llegó ninguna, para que el controlador pueda avisar.
     */
    private function parseOptions(string $type, ?string $rawOptions): array|null|false
    {
        if (! in_array($type, FormField::TYPES_CON_OPCIONES, true)) {
            return null;
        }

        $options = collect(preg_split('/\r\n|\r|\n/', (string) $rawOptions))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();

        return empty($options) ? false : $options;
    }
}
