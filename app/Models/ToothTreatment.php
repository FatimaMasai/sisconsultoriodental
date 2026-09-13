<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un tratamiento cargado sobre una pieza dental puntual del odontograma de
 * un Expediente (ej: "16 - Resina - 170 Bs - realizado"). No pertenece a
 * una Consulta puntual: el odontograma es el estado de toda la boca del
 * paciente a lo largo del tiempo, con su propia fecha.
 */
class ToothTreatment extends Model
{
    protected $fillable = [
        'expediente_id',
        'tooth_number',
        'treatment',
        'price',
        'completed',
        'date',
        'sale_id',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'price' => 'decimal:2',
        'date' => 'date',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    // Venta que cobró este tratamiento (si ya se vinculó una). El cobro en
    // sí se hace siempre desde el módulo de Ventas; acá solo queda la
    // referencia para saber, de un vistazo, qué piezas ya se cobraron.
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
