<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite vincular un tratamiento del odontograma a la Venta que lo cobró
 * (ver ExpedienteController::linkToothTreatmentSale). El cobro en sí sigue
 * pasando siempre por el módulo de Ventas (Servicios del catálogo,
 * Contado/Crédito) tal como ya funciona hoy; este campo solo guarda la
 * referencia para que el odontograma muestre qué piezas ya se cobraron.
 * Nullable y nullOnDelete: si la venta se anula/borra, el tratamiento no
 * desaparece, solo queda "sin cobrar" otra vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tooth_treatments', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->after('completed')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tooth_treatments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sale_id');
        });
    }
};
