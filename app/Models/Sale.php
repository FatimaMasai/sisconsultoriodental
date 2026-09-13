<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{

    protected $fillable = [
        'sale_date',
        //'description',
        'total',
        'discount', // Bs. de descuento restado del subtotal para llegar a "total"
        'status',

        'payment_type',   // Contado | Credito
        'initial_amount', // cuota inicial cuando payment_type = Credito (0 en ventas al Contado)

        'patient_id',
        'doctor_id',
    ];

    //uno a muchos inversa
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    //uno a muchos inversa
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }


    //relacion uno a muchos
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // relacion uno a muchos: cuotas generadas cuando la venta es a Credito
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function isCredito(): bool
    {
        return $this->payment_type === 'Credito';
    }

    /**
     * Número de comprobante de la venta para mostrar al usuario, ej: "V-0001".
     * Se genera a partir del id interno, no se guarda en la base de datos.
     */
    public function getNumeroAttribute(): string
    {
        return 'V-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Subtotal antes del descuento (suma de los servicios vendidos). No se
     * guarda en la base de datos: "total" ya queda guardado con el
     * descuento restado, así que el subtotal siempre se puede reconstruir
     * sumándole de nuevo el descuento.
     */
    public function getSubtotalAttribute(): float
    {
        return round((float) $this->total + (float) $this->discount, 2);
    }

    /**
     * Saldo pendiente de pago de la venta (solo aplica a ventas a Credito).
     *
     * Desde que los pagos a Crédito pasaron a ser "abonos libres" (cualquier
     * monto, cualquier fecha, sin plan de cuotas fijo), el saldo es
     * simplemente el total menos todo lo que se pagó y no fue anulado. Esta
     * misma fórmula sigue dando el resultado correcto para ventas viejas que
     * todavía tienen cuota inicial y/o cuotas generadas por el sistema
     * anterior, porque esos montos también quedaron registrados como Payment
     * (con payment_status 'Cuota Inicial' o 'Cuota').
     */
    public function getSaldoPendienteAttribute(): float
    {
        if (! $this->isCredito()) {
            return 0;
        }

        $pagado = $this->payments->where('payment_status', '!=', 'Anulado')->sum('amount');

        return round($this->total - $pagado, 2);
    }

    /**
     * Estado de la deuda de una venta a Credito, para saber de un vistazo
     * si el paciente ya terminó de pagar o todavía debe:
     *   - null        => la venta es al Contado, no aplica.
     *   - 'Anulado'   => la venta fue anulada, no hay nada que cobrar.
     *   - 'Completado'=> el saldo pendiente ya es 0 (o menos).
     *   - 'Pendiente' => todavía queda saldo por cobrar.
     *
     * Antes esto distinguía además "Vencida" vs "Al día" según la fecha de
     * vencimiento de cada cuota del plan fijo. Con los abonos libres ya no
     * hay fechas de vencimiento que respetar, así que esa distinción se cae:
     * ahora es simplemente "debe" o "no debe".
     */
    public function getEstadoCreditoAttribute(): ?string
    {
        if (! $this->isCredito()) {
            return null;
        }

        if ($this->status == 0) {
            return 'Anulado';
        }

        return $this->saldo_pendiente <= 0 ? 'Completado' : 'Pendiente';
    }

}
