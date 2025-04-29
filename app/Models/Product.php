<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price', 
        'status',
        'product_category_id',
    ];
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    //relacion uno a muchos
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

}
