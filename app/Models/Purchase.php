<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'total',
        'status',

        'supplier_id',
    ];

    public function supplier() //proveedor
    {
        return $this->belongsTo(Supplier::class);
    }

    //relacion uno a muchos
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }


}
