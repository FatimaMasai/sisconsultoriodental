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

    /**
     * Número de comprobante de la compra para mostrar al usuario, ej: "C-0001".
     * Se genera a partir del id interno, no se guarda en la base de datos.
     */
    public function getNumeroAttribute(): string
    {
        return 'C-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

}
