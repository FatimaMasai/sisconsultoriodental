<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.appointments.index')->only('index', 'events');
        $this->middleware('can:admin.appointments.create')->only('create', 'store');
        $this->middleware('can:admin.appointments.edit')->only('edit', 'update', 'confirm', 'complete');
        $this->middleware('can:admin.appointments.cancel')->only('cancel');
    }

    /**
     * Vista con el calendario. Los eventos se cargan por AJAX desde events().
     */
    public function index()
    {
        $doctors = Doctor::with('person')->where('status', 1)->orderBy('id', 'desc')->get();

        return view('admin.appointments.index', compact('doctors'));
    }

    /**
     * Devuelve las citas en el formato que espera FullCalendar.
     */
    public function events(Request $request)
    {
        $query = Appointment::with(['patient.person', 'doctor.person', 'service']);

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->get();

        $events = $appointments->map(function (Appointment $appointment) {
            $patientName = trim($appointment->patient->person->name . ' ' . $appointment->patient->person->last_name_father);

            return [
                'id' => $appointment->id,
                'title' => $patientName . ($appointment->service ? ' · ' . $appointment->service->name : ''),
                'start' => $appointment->starts_at->toIso8601String(),
                'end' => $appointment->ends_at->toIso8601String(),
                'color' => $appointment->color,
                'url' => route('admin.appointments.edit', $appointment),
            ];
        });

        return response()->json($events);
    }

    public function create(Request $request)
    {
        $patients = Patient::with('person')->where('status', 1)->orderBy('id', 'desc')->get();
        $doctors = Doctor::with('person')->where('status', 1)->orderBy('id', 'desc')->get();
        $services = Service::where('status', 1)->orderBy('id', 'desc')->get();

        $prefillDate = $request->query('date');

        return view('admin.appointments.create', compact('patients', 'doctors', 'services', 'prefillDate'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAppointment($request);

        $this->assertNoOverlap($data['doctor_id'], $data['starts_at'], $data['ends_at']);

        $appointment = Appointment::create($data + [
            'status' => 'Programada',
            'created_by' => auth()->id(),
        ]);

        session()->flash('swal', [
            'title' => 'Cita agendada',
            'text' => 'La cita se registró correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.appointments.index');
    }

    public function edit(Appointment $appointment)
    {
        $patients = Patient::with('person')->where('status', 1)->orderBy('id', 'desc')->get();
        $doctors = Doctor::with('person')->where('status', 1)->orderBy('id', 'desc')->get();
        $services = Service::where('status', 1)->orderBy('id', 'desc')->get();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'doctors', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $data = $this->validateAppointment($request);

        $this->assertNoOverlap($data['doctor_id'], $data['starts_at'], $data['ends_at'], $appointment->id);

        $appointment->update($data);

        session()->flash('swal', [
            'title' => 'Cita actualizada',
            'text' => 'Los cambios se guardaron correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.appointments.index');
    }

    public function cancel(Appointment $appointment)
    {
        if ($appointment->isCancelled()) {
            return redirect()->back()->with('info', 'Esta cita ya está cancelada.');
        }

        $appointment->update(['status' => 'Cancelada']);

        session()->flash('swal', [
            'title' => 'Cita cancelada',
            'text' => 'La cita fue cancelada correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->back();
    }

    public function confirm(Appointment $appointment)
    {
        $appointment->update(['status' => 'Confirmada']);

        return redirect()->back()->with('info', 'Cita marcada como confirmada.');
    }

    public function complete(Appointment $appointment)
    {
        $appointment->update(['status' => 'Completada']);

        return redirect()->back()->with('info', 'Cita marcada como completada.');
    }

    private function validateAppointment(Request $request): array
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'nullable|exists:services,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startsAt = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endsAt = $startsAt->copy()->addMinutes((int) $validated['duration_minutes']);

        return [
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'service_id' => $validated['service_id'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    /**
     * Evita que un doctor quede agendado dos veces al mismo tiempo.
     * Las citas canceladas no cuentan para el choque de horarios.
     */
    private function assertNoOverlap(int $doctorId, $startsAt, $endsAt, ?int $ignoreId = null): void
    {
        $overlaps = Appointment::where('doctor_id', $doctorId)
            ->where('status', '!=', 'Cancelada')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($overlaps) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_time' => 'Ese doctor ya tiene otra cita en ese horario.',
            ]);
        }
    }
}
