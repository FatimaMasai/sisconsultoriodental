<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'payment_method',
        'payment_status',

        'sale_id',
        'purchase_id',
        'installment_id',
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
