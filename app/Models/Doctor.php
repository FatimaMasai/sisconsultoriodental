<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{

    protected $fillable = [
        'status',
        'person_id',
        'user_id',
        'speciality_id',
    ];
    //uno a uno inversa
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    //uno a muchos
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }


    //uno a muchos inversa
    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    //uno a muchos
    public function histories()
    {
        return $this->hasMany(History::class);
    }

    /**
     * Cuenta de acceso (login) vinculada a este doctor. Es opcional: si es
     * null, este registro es solo informativo y no aplica ninguna
     * restricción de especialidad al iniciar sesión.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Especialidades ajenas a la suya que un administrador le autorizó ver.
     */
    public function specialityAccessGrants()
    {
        return $this->hasMany(SpecialityAccessGrant::class);
    }

    /**
     * IDs de las especialidades adicionales autorizadas (sin incluir la propia).
     */
    public function grantedSpecialityIds(): array
    {
        return $this->specialityAccessGrants()->pluck('speciality_id')->all();
    }

    /**
     * ¿Puede este doctor ver el historial clínico de la especialidad dada?
     * Siempre puede ver la suya propia, y además cualquiera que le hayan
     * autorizado explícitamente.
     */
    public function canViewSpeciality(?int $specialityId): bool
    {
        if (! $specialityId) {
            return true;
        }

        if ((int) $this->speciality_id === (int) $specialityId) {
            return true;
        }

        return in_array($specialityId, $this->grantedSpecialityIds(), true);
    }
}
