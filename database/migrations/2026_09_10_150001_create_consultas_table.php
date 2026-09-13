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
        // Cada visita del paciente dentro de un expediente. Sustituye el
        // registro "un historial por cada servicio vendido": ahora una
        // venta con varios servicios genera una sola Consulta.
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained(); // quién atendió esta consulta puntual
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete(); // venta que la originó, si aplica

            $table->string('description')->nullable();
            $table->date('date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
