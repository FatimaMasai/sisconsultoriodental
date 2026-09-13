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
        Schema::table('doctors', function (Blueprint $table) {
            // Vincula el registro de Doctor con la cuenta de acceso (User) que
            // usa para iniciar sesión. Es opcional: un doctor sin cuenta
            // vinculada no queda sujeto a la restricción de especialidad.
            $table->foreignId('user_id')->nullable()->unique()->after('person_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
