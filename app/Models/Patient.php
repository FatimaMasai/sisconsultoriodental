<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{

    protected $fillable = [
        'allergy',
        'observation',
        'recommended_by', 
        'responsible_person',
        'medical_history', //antecedentes
        'status',
        'person_id',
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
    

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    //uno a muchos
    public function histories()
    {
        return $this->hasMany(History::class);
    }
}
