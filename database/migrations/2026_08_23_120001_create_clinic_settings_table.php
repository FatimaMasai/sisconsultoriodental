<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Configuración de la clínica: guarda los tokens de la cuenta de
     * Google conectada para sincronizar citas con Google Calendar.
     *
     * Se usa como una tabla de una sola fila (siempre id = 1), leída y
     * escrita a través de App\Models\ClinicSetting::instance().
     * Los tokens se guardan encriptados (cast 'encrypted' en el modelo).
     */
    public function up(): void
    {
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->string('google_calendar_id')->default('primary');
            $table->text('google_access_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->timestamp('google_token_expires_at')->nullable();
            $table->string('google_account_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
    }
};
