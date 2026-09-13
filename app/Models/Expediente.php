<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Agrupa, por especialidad, todo el historial clínico de un paciente.
 * Es único por paciente+especialidad: si esa especialidad tiene varios
 * doctores, todos comparten el mismo expediente de un paciente dado.
 */
class Expediente extends Model
{
    protected $fillable = [
        'patient_id',
        'speciality_id',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class)->orderByDesc('date')->orderByDesc('id');
    }

    // Tratamientos del odontograma (por pieza dental), de más nuevo a más
    // viejo. No dependen de una Consulta puntual.
    public function toothTreatments()
    {
        return $this->hasMany(ToothTreatment::class)->orderByDesc('date')->orderByDesc('id');
    }
}
