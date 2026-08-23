<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable = [
        'number',
        'due_date',
        'amount',
        'status',
        'paid_at',

        'sale_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // relacion inversa: la cuota pertenece a una venta
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // por si en el futuro se permiten pagos parciales de una misma cuota
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Estado real de la cuota calculando "Vencida" según la fecha actual,
     * sin depender de un job programado que actualice la BD.
     */
    public function getEstadoActualAttribute(): string
    {
        if ($this->status === 'Pagada') {
            return 'Pagada';
        }

        if ($this->status === 'Anulada') {
            return 'Anulada';
        }

        if ($this->due_date && $this->due_date->isPast()) {
            return 'Vencida';
        }

        return 'Pendiente';
    }
}
