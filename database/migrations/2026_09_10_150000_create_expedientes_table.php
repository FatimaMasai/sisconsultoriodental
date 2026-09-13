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
        // Un expediente agrupa, por especialidad, todo el historial clínico
        // de un paciente. Si una especialidad tiene varios doctores, todos
        // comparten el mismo expediente de un paciente: lo que cambia por
        // visita es quién lo atendió (ver tabla `consultas`).
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speciality_id')->constrained('specialities')->cascadeOnDelete();
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['patient_id', 'speciality_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
