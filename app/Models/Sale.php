<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{

    protected $fillable = [
        'sale_date', 
        //'description',
        'total',
        'status',

        'patient_id',
        'doctor_id',
    ];
    
    //uno a muchos inversa
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    //uno a muchos inversa
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }


    //relacion uno a muchos
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
 
}
