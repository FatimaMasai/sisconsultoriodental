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
            // Descuento en Bs. aplicado sobre el total de la venta (0 = sin
            // descuento). "total" ya queda guardado CON el descuento restado
            // (es el monto real de la venta), así que todo lo que ya lee
            // $sale->total (saldo pendiente, reportes, PDF) sigue funcionando
            // sin cambios; este campo es solo para poder mostrar el desglose
            // (subtotal, descuento, total) en el comprobante y el detalle.
            $table->decimal('discount', 10, 2)->default(0)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
