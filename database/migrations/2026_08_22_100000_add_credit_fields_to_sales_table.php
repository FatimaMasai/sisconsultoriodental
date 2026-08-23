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
        Schema::table('sales', function (Blueprint $table) {
            // Tipo de venta: Contado (pago único) o Credito (pago en cuotas)
            $table->string('payment_type')->default('Contado')->after('total');

            // Monto de la cuota inicial cuando la venta es a Credito (0 para ventas al Contado)
            $table->decimal('initial_amount', 10, 2)->default(0)->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'initial_amount']);
        });
    }
};
