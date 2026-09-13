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
        // Excepciones de acceso: permite que un doctor vea también el
        // historial clínico de pacientes de OTRA especialidad además de la
        // suya, cuando un administrador se lo autoriza explícitamente.
        Schema::create('speciality_access_grants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speciality_id')->constrained('specialities')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable(); // motivo de la autorización (opcional)

            $table->timestamps();

            $table->unique(['doctor_id', 'speciality_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speciality_access_grants');
    }
};
