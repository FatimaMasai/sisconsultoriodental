<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            // Marca a qué Consulta (nueva estructura de expedientes) quedó
            // convertido este historial. Sirve para no migrarlo dos veces y
            // para poder enlazar desde las pantallas antiguas mientras
            // conviven ambas estructuras.
            $table->foreignId('consulta_id')->nullable()->after('service_id')
                ->constrained('consultas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consulta_id');
        });
    }
};
