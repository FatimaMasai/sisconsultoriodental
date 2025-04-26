<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{

    protected $fillable = [
        'status',
        'person_id',
        'speciality_id', 
    ];
    //uno a uno inversa
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    //uno a muchos
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }


    //uno a muchos inversa
    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }
}
