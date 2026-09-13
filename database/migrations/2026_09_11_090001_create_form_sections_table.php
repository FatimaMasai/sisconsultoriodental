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
        // Una sección agrupa campos dentro de una plantilla (ej: "Datos
        // antropométricos", "Antecedentes"). `grupo_visual` es una
        // subagrupación opcional DENTRO de la sección, para casos como los
        // tests con puntaje donde la vista se organiza distinto de cómo se
        // suman los resultados (ver Perfil Neurofuncional).
        Schema::create('form_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('grupo_visual')->nullable();
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_sections');
    }
};
