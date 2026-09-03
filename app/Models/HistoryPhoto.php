<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryPhoto extends Model
{
    protected $fillable = [
        'history_id',
        'type',
        'path',
    ];

    public function history()
    {
        return $this->belongsTo(History::class);
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
