<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bitácora de auditoría. Por ahora solo se usa para registrar
     * anulaciones de ventas y compras (quién anuló qué y cuándo), que es
     * el riesgo principal de plata en el sistema. 'auditable_type' +
     * 'auditable_id' apuntan al registro afectado (polimórfico) para que
     * más adelante se puedan registrar otro tipo de acciones sin tener
     * que tocar la tabla de nuevo.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // ej: 'sale.cancelled', 'purchase.cancelled'
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
