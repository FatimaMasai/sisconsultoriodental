<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\History;
use App\Models\HistoryPhoto;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        // $this->middleware('auth');
        $this->middleware('can:admin.histories.index')->only('index');
        $this->middleware('can:admin.histories.create')->only('create', 'store');
        $this->middleware('can:admin.histories.show')->only('show'); 
        
        $this->middleware('can:admin.histories.edit')->only('edit', 'update');
        $this->middleware('can:admin.histories.destroy')->only('destroy');

        $this->middleware('can:admin.histories.addNote')->only('addNote');
        $this->middleware('can:admin.histories.pdf')->only('pdf');

        $this->middleware('can:admin.histories.photos.store')->only('storePhoto');
        $this->middleware('can:admin.histories.photos.destroy')->only('destroyPhoto');
    }
    public function index()
    {
        // El listado y la búsqueda en tiempo real los maneja el componente
        // Livewire admin.history-search (ver app/Livewire/Admin/HistorySearch.php).
        return view('admin.histories.index');
    }
    /**
     * Show the form for creating a new resource.
     * 
     * 
     */
    public function create()
    {
 

        $patients = Patient::with('person')->where('status',1)->get(); 
        $doctors = Doctor::with('person')->where('status',1)->get();

        $services = Service::where('status', 1)->get(); // Obtener servicios activos

        return view('admin.histories.create', compact('patients', 'services', 'doctors'));
    } 

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validar los datos que llegan del formulario
    $request->validate([
        'description' => 'required|string|max:1000',
        'patient_id' => 'required|exists:patients,id',
        'doctor_id' => 'required|exists:doctors,id',
        'service_id' => 'required|exists:services,id',
    ]);

    // Crear el nuevo historial
    History::create([
        'description' => $request->description,
        'date' => now(), // Fecha actual
        'patient_id' => $request->patient_id,
        'doctor_id' => $request->doctor_id,
        'service_id' => $request->service_id,
    ]);

    session()->flash('swal', [
        'title' => 'Historial Médico creado',
        'text' => 'Se registró el historial correctamente.',
        'icon' => 'success'
    ]);

    // Redirigir de nuevo al listado de historiales
    return redirect()->route('admin.histories.index');
}


    /**
     * Display the specified resource.
     */ 

     public function show($id)
    {
        $history = History::with('patient.person', 'doctor.person', 'service', 'photos')->findOrFail($id);
           // Asegurarse de que las notas estén ordenadas de forma descendente
        $history->notes = $history->notes()->orderBy('created_at', 'desc')->get();

        return view('admin.histories.show', compact('history'));
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(History $history)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, History $history)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(History $history)
    {
        //
    }

    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $history = History::findOrFail($id);

        $history->notes()->create([
            'note' => $request->note,
        ]);

        session()->flash('swal', [
            'title' => 'Nota agregada',
            'text' => 'Se ha registrado una nueva consulta.',
            'icon' => 'success'
        ]);

        return redirect()->route('admin.histories.show', $history->id);
    }

    /**
     * Sube una o varias fotos (Antes/Después) para un historial médico.
     */
    public function storePhoto(Request $request, $id)
    {
        $history = History::findOrFail($id);

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
            $path = $file->store('histories/' . $history->id, 'public');

            HistoryPhoto::create([
                'history_id' => $history->id,
                'type' => $request->type,
                'path' => $path,
            ]);
        }

        session()->flash('swal', [
            'title' => 'Fotos agregadas',
            'text' => 'Se guardaron las fotos correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.histories.show', $history->id);
    }

    /**
     * Elimina una foto (Antes/Después) de un historial médico.
     */
    public function destroyPhoto($id)
    {
        $photo = HistoryPhoto::findOrFail($id);
        $historyId = $photo->history_id;

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        session()->flash('swal', [
            'title' => 'Foto eliminada',
            'text' => 'Se eliminó la foto correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.histories.show', $historyId);
    }

    public function pdf($id)
    {
        $history = History::with('patient.person', 'doctor.person', 'service', 'notes')->findOrFail($id);

        // Generar el PDF
        $pdf = PDF::loadView('admin.histories.pdf', compact('history'))
                ->setPaper('a4', 'portrait'); // Establecer el tamaño de página (A4) y la orientación

        // Descargar el PDF
        return $pdf->stream('historial_medico_' . $history->patient->person->name . '.pdf');
    }




}
