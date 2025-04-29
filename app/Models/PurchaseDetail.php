<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'price',
        'quantity',
        'subtotal', 

        'purchase_id', //compra
        'product_id',
    ];

    //relacion uno a muchos
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
