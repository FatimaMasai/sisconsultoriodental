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
        // Un campo individual dentro de una sección. `type` es un string
        // libre en vez de un enum de base de datos, para poder sumar tipos
        // nuevos más adelante sin migraciones (la lista válida vive en
        // App\Models\FormField::TYPES). `options` guarda, según el tipo,
        // las alternativas de selección o las columnas de una tabla.
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_section_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('type');
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->string('help_text')->nullable();
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
