<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Una receta médica escrita durante una consulta puntual. El contenido es
 * texto libre (medicamento, dosis, frecuencia, indicaciones...): el doctor
 * la redacta como la escribiría a mano, y el sistema solo la guarda,
 * la ordena por fecha y la deja lista para imprimir.
 */
class Receta extends Model
{
    protected $fillable = [
        'consulta_id',
        'contenido',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}
