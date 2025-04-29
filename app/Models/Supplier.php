<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    protected $fillable = [
        'company',
        'nit', 
        'status',

        'person_id',

    ];

    //uno a uno inversa
    public function person()
    {
        return $this->belongsTo(Person::class);
    }


    public function purchases() //compras
    {
        return $this->hasMany(Purchase::class);
    }
}
