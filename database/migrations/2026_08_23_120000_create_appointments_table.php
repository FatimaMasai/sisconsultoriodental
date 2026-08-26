<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de citas (agenda). Una cita agenda un paciente con un doctor
     * en un rango de fecha/hora, opcionalmente ligada a un servicio.
     *
     * Estados posibles (campo 'status'):
     *   - 'Programada'  => recién creada, todavía no confirmada.
     *   - 'Confirmada'  => el paciente confirmó que va a venir.
     *   - 'Completada'  => el paciente vino y se atendió.
     *   - 'Cancelada'   => se canceló, no se atiende.
     *   - 'No asistio'  => el paciente no llegó.
     *
     * 'reminder_sent_at' marca cuándo se mandó el recordatorio por
     * WhatsApp, para no mandarlo dos veces.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status')->default('Programada');
            $table->text('notes')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['doctor_id', 'starts_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
