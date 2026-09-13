<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado Civil es un dato de la persona (como Sexo o Fecha de
     * Nacimiento), no algo específico de una especialidad. Antes vivía
     * únicamente dentro del formulario dinámico de Medicina Ortomolecular
     * (Historia Clínica Funcional), así que no aparecía para pacientes
     * atendidos en otras especialidades. Se mueve acá para que quede
     * disponible para todos los pacientes, junto al resto de sus datos
     * personales.
     */
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('civil_status')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('civil_status');
        });
    }
};
