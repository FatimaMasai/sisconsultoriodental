<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaNote extends Model
{
    protected $fillable = [
        'consulta_id',
        'note',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}
