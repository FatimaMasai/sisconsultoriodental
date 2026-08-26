<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'service_id',
        'starts_at',
        'ends_at',
        'status',
        'notes',
        'reminder_sent_at',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelada';
    }

    /**
     * Color usado en el calendario (FullCalendar) según el estado de la cita.
     */
    public function getColorAttribute(): string
    {
        return match ($this->status) {
            'Confirmada' => '#2563eb', // azul
            'Completada' => '#16a34a', // verde
            'Cancelada' => '#9ca3af', // gris
            'No asistio' => '#dc2626', // rojo
            default => '#d97706', // ámbar (Programada)
        };
    }
}
