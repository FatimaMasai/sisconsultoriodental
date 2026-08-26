<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    protected $fillable = [
        'google_calendar_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_account_email',
    ];

    protected $casts = [
        'google_access_token' => 'encrypted',
        'google_refresh_token' => 'encrypted',
        'google_token_expires_at' => 'datetime',
    ];

    /**
     * La configuración de la clínica vive en una sola fila (id = 1).
     * Este helper la trae (o la crea vacía si todavía no existe).
     */
    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function isGoogleConnected(): bool
    {
        return ! empty($this->google_refresh_token);
    }
}
