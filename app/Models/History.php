<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{

    protected $fillable = [
        'description',
        'date',

        'patient_id',
        'doctor_id',
        'service_id',
        'consulta_id',
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

    public function notes()
{
    return $this->hasMany(HistoryNote::class);
}

    public function photos()
    {
        return $this->hasMany(HistoryPhoto::class);
    }

    /**
     * Consulta (nueva estructura de Expedientes) a la que quedó convertido
     * este historial, una vez corrido `php artisan historial:migrar-expedientes`.
     */
    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

}
