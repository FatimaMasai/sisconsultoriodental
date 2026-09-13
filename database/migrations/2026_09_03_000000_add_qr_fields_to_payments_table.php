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
        Schema::table('payments', function (Blueprint $table) {
            // Identificador del movimiento que devuelve VeriPagos al generar el QR,
            // para poder auditar/conciliar el pago (nulo en pagos que no fueron por QR).
            $table->string('qr_movimiento_id')->nullable()->after('payment_method');

            // Datos del remitente que devuelve VeriPagos al verificar el estado del QR
            // (nombre, banco, documento, cuenta), guardados tal cual para el comprobante.
            $table->json('qr_remitente')->nullable()->after('qr_movimiento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['qr_movimiento_id', 'qr_remitente']);
        });
    }
};
