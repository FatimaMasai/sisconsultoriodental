<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hay tratamientos del odontograma que no van sobre una pieza puntual
     * (ej: "Limpieza general", "Fluorización"). Antes "tooth_number" era
     * obligatorio en la base, lo que forzaba a marcar SIEMPRE un diente en
     * el gráfico aunque no correspondiera. Se deja sin marcar (null) para
     * esos casos; se sigue mostrando el diente cuando sí se cargó uno.
     */
    public function up(): void
    {
        Schema::table('tooth_treatments', function (Blueprint $table) {
            $table->string('tooth_number', 5)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tooth_treatments', function (Blueprint $table) {
            $table->string('tooth_number', 5)->nullable(false)->change();
        });
    }
};
