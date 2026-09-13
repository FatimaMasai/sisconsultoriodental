<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Excepción de acceso: autoriza a un Doctor a ver también el historial
 * clínico de pacientes de una especialidad que no es la suya.
 */
class SpecialityAccessGrant extends Model
{
    protected $fillable = [
        'doctor_id',
        'speciality_id',
        'granted_by',
        'note',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
