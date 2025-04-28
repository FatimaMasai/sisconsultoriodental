<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    
    protected $fillable = [
        'description',
        'date',

        'patient_id',
        'doctor_id',
        'service_id',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function notes()
{
    return $this->hasMany(HistoryNote::class);
}
    

}
