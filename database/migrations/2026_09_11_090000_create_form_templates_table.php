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
        // Una plantilla de historial define, para una especialidad, qué
        // secciones y campos se llenan al registrar una consulta (el
        // "constructor de formularios sin código"). Puede haber varias
        // versiones de la plantilla de una misma especialidad con el
        // tiempo; solo la que está "activa" es la que se usa para llenar
        // consultas nuevas.
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('speciality_id')->constrained('specialities')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_templates');
    }
};
