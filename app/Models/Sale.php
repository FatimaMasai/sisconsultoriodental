<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{

    protected $fillable = [
        'sale_date',
        //'description',
        'total',
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
     * Saldo pendiente de pago de la venta (solo aplica a ventas a Credito).
     */
    public function getSaldoPendienteAttribute(): float
    {
        if (! $this->isCredito()) {
            return 0;
        }

        $pagadoEnCuotas = $this->installments->where('status', 'Pagada')->sum('amount');

        // Pagos que se cobran de inmediato pero no son la cuota inicial del financiamiento
        // ni el registro de una cuota ya pagada (por ejemplo, la Consulta, que siempre se
        // cobra de una vez aunque el resto de la venta sea a crédito).
        $pagadoDeInmediato = $this->payments
            ->whereNotIn('payment_status', ['Cuota Inicial', 'Cuota', 'Anulado'])
            ->sum('amount');

        return round($this->total - $this->initial_amount - $pagadoDeInmediato - $pagadoEnCuotas, 2);
    }

    /**
     * Estado de la deuda de una venta a Credito, para saber de un vistazo
     * si el paciente ya terminó de pagar o todavía debe:
     *   - null        => la venta es al Contado, no aplica.
     *   - 'Anulado'   => la venta fue anulada, no hay nada que cobrar.
     *   - 'Completado'=> todas las cuotas están pagadas, el paciente ya no debe.
     *   - 'Pendiente' => tiene al menos una cuota vencida sin pagar (no se cobra mora, solo queda pendiente).
     *   - 'Al día'    => le quedan cuotas pendientes, pero ninguna vencida todavía.
     */
    public function getEstadoCreditoAttribute(): ?string
    {
        if (! $this->isCredito()) {
            return null;
        }

        $cuotas = $this->installments;

        if ($cuotas->isEmpty()) {
            // No se generó ningún plan de cuotas (por ejemplo, una venta a crédito donde
            // todo lo vendido era la Consulta, que se cobra de inmediato): no queda nada por pagar.
            return 'Completado';
        }

        $cuotasActivas = $cuotas->where('status', '!=', 'Anulada');

        if ($cuotasActivas->isEmpty()) {
            return 'Anulado';
        }

        if ($cuotasActivas->every(fn (Installment $cuota) => $cuota->status === 'Pagada')) {
            return 'Completado';
        }

        $tieneVencidas = $cuotasActivas->contains(fn (Installment $cuota) => $cuota->estado_actual === 'Vencida');

        return $tieneVencidas ? 'Pendiente' : 'Al día';
    }

}
