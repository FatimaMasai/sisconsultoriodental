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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->date('sale_date');
            //$table->string('description'); 
            $table->decimal('total', 10, 2); // Total de la venta
            $table->string('status')->default('true');
            


            // $table->enum('payment_method', ['cash', 'bank_transfer']); // Métodos de pago
            // $table->enum('status', ['paid', 'unpaid'])->default('unpaid'); // Estado de pago

            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
