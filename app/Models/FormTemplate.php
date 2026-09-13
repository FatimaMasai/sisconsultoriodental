<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Plantilla del historial de una especialidad: define qué secciones y
 * campos se llenan al registrar una Consulta. Puede haber varias versiones
 * en el tiempo; solo la activa se usa para consultas nuevas.
 */
class FormTemplate extends Model
{
    protected $fillable = [
        'speciality_id',
        'name',
        'version',
        'status',
    ];

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function sections()
    {
        return $this->hasMany(FormSection::class)->orderBy('order');
    }
}
