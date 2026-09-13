<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'payment_method',
        'payment_status',

        // Datos de conciliación cuando el pago se hizo por QR (VeriPagos).
        'qr_movimiento_id',
        'qr_remitente',

        'sale_id',
        'purchase_id',
        'installment_id',
    ];

    protected $casts = [
        'qr_remitente' => 'array',
    ];
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }
}
