<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{ 
    protected $fillable = [
        'name',
        'last_name_father',
        'last_name_mother', 
        'identity_card',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'status',
        'user_id',
    ];

    //uno a uno inversa
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //uno a muchos
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }


}
