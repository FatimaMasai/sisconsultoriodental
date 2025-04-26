<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    //uno a muchos
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
