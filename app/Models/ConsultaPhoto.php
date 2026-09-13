<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaPhoto extends Model
{
    protected $fillable = [
        'consulta_id',
        'type',
        'path',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    /**
     * URL pública para mostrar la foto en el navegador.
     * Requiere que exista el enlace simbólico de storage
     * (ver `php artisan storage:link`).
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
