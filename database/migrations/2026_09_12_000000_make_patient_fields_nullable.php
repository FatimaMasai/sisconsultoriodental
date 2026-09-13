<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alergia, Observación, Recomendado por, Responsable y Antecedentes
     * dejan de ser obligatorios al registrar/editar un paciente: se pueden
     * dejar en blanco (ver validación en PatientController).
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('allergy')->nullable()->change();
            $table->string('observation')->nullable()->change();
            $table->string('recommended_by')->nullable()->change();
            $table->string('responsible_person')->nullable()->change();
            $table->string('medical_history')->nullable()->change(); //antecedentes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('allergy')->nullable(false)->change();
            $table->string('observation')->nullable(false)->change();
            $table->string('recommended_by')->nullable(false)->change();
            $table->string('responsible_person')->nullable(false)->change();
            $table->string('medical_history')->nullable(false)->change();
        });
    }
};
