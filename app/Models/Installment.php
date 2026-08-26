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

    /**
     * Calcula el monto de cada cuota para financiar $saldoFinanciado en
     * $installmentsCount cuotas "iguales". La última cuota absorbe el
     * ajuste de redondeo para que la suma cuadre exacto con el saldo
     * (evita el clásico bug de "las cuotas suman 999.99 en vez de 1000").
     *
     * Devuelve un array indexado desde 0, un monto (float, 2 decimales)
     * por cuota, en el mismo orden que las cuotas 1..N.
     *
     * Usado por SaleController::store() al generar el plan de cuotas de
     * una venta a Crédito, y cubierto por tests en tests/Unit para que un
     * cambio futuro no rompa silenciosamente el cálculo con plata real.
     */
    public static function planAmounts(float $saldoFinanciado, int $installmentsCount): array
    {
        if ($installmentsCount < 1) {
            throw new \InvalidArgumentException('El número de cuotas debe ser al menos 1.');
        }

        $montoBase = round($saldoFinanciado / $installmentsCount, 2);
        $acumulado = 0;
        $amounts = [];

        for ($i = 1; $i <= $installmentsCount; $i++) {
            $monto = $i < $installmentsCount
                ? $montoBase
                : round($saldoFinanciado - $acumulado, 2);

            $acumulado += $monto;
            $amounts[] = $monto;
        }

        return $amounts;
    }
}
