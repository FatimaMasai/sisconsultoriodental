<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Una visita del paciente dentro de un expediente. Reemplaza al registro
 * "un historial por cada servicio vendido": una venta con varios servicios
 * genera una sola Consulta.
 */
class Consulta extends Model
{
    protected $fillable = [
        'expediente_id',
        'doctor_id',
        'sale_id',
        'form_template_id',
        'description',
        'date',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    // Con qué plantilla (y versión exacta) se llenaron los campos de
    // "data": aunque la plantilla cambie después, esta consulta se sigue
    // mostrando con la estructura de campos que tenía en su momento.
    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    // Doctor que atendió esta consulta puntual (puede no coincidir con
    // otras consultas del mismo expediente si la especialidad tiene
    // varios doctores).
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function notes()
    {
        return $this->hasMany(ConsultaNote::class);
    }

    public function photos()
    {
        return $this->hasMany(ConsultaPhoto::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }
}
