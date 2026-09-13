<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Agrupa campos dentro de una plantilla (ej: "Datos antropométricos").
 * `grupo_visual` permite una subagrupación opcional dentro de la sección,
 * útil para tests con puntaje donde la vista no coincide 1 a 1 con cómo se
 * suman los resultados.
 */
class FormSection extends Model
{
    protected $fillable = [
        'form_template_id',
        'title',
        'grupo_visual',
        'order',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }
}
