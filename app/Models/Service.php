<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable =[
        'name',
        'price',
        'status',
        
        'service_category_id',
    ];
    //uno a muchos inversa
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
