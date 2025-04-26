<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{

    protected $fillable = [
        
        'price',
        'quantity',
        'subtotal', 

        'sale_id',
        'service_id',
    ];
    //relacion uno a muchos
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }


    public function service()
    {
        return $this->belongsTo(Service::class);
    }

}
