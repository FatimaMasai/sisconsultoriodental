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
        Schema::table('consultas', function (Blueprint $table) {
            // Con qué plantilla (y versión exacta) se llenó esta consulta:
            // así, si la plantilla cambia después, las consultas viejas
            // se siguen mostrando con la estructura de campos que tenían
            // en su momento, no con la actual.
            $table->foreignId('form_template_id')->nullable()->after('sale_id')
                ->constrained('form_templates')->nullOnDelete();

            // Respuestas de los campos de la plantilla, guardadas como
            // {"<form_field_id>": valor, ...}.
            $table->json('data')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('form_template_id');
            $table->dropColumn('data');
        });
    }
};
