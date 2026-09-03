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
        Schema::table('people', function (Blueprint $table) {
            $table->string('last_name_mother')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // No se puede volver a NOT NULL de forma segura si ya existen registros
            // sin apellido materno; se deja como nullable también en el rollback.
            $table->string('last_name_mother')->nullable(false)->change();
        });
    }
};
