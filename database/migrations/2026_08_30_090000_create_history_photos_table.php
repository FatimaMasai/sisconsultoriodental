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
        Schema::create('history_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('history_id')->constrained()->onDelete('cascade');
            // 'antes' o 'despues': a qué momento del tratamiento corresponde la foto.
            $table->enum('type', ['antes', 'despues']);
            // Ruta relativa dentro del disco 'public' (storage/app/public/...).
            $table->string('path');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_photos');
    }
};
