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
        Schema::create('tooth_treatments', function (Blueprint $table) {
            $table->id();

            // Va contra el Expediente (no contra una Consulta puntual):
            // el odontograma es el estado de toda la boca del paciente a lo
            // largo del tiempo, no algo que pertenezca a una sola visita.
            $table->foreignId('expediente_id')->constrained()->cascadeOnDelete();

            $table->string('tooth_number', 5); // Numeración FDI, ej: "16", "55"
            $table->string('treatment');
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('completed')->default(false);
            $table->date('date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tooth_treatments');
    }
};
