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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            
            $table->decimal('amount', 10, 2); // Monto del pago
            $table->string('payment_method'); // Métodos de pago
            $table->string('payment_status'); // Estado del pago

            $table->foreignId('sale_id')->constrained()->onDelete('cascade'); // Relación con la tabla sales

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
