<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega a clinic_settings (la fila única de configuración de la
     * clínica) los campos de apariencia: el logo subido desde el panel
     * (Configuración > Apariencia) y el color de marca usado en el menú,
     * encabezados y botón principal de cada pantalla.
     *
     * logo_path guarda la ruta relativa dentro del disco "public"
     * (storage/app/public/branding/...), no la ruta absoluta ni la URL.
     * Si es null, el sistema sigue usando public/images/logo.png como
     * logo por defecto (ver App\Models\ClinicSetting::logoUrl()/logoBase64()).
     */
    public function up(): void
    {
        Schema::table('clinic_settings', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('id');
            $table->string('primary_color', 7)->default('#0d9488')->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'primary_color']);
        });
    }
};
