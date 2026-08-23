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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('number'); // numero de cuota: 1, 2, 3...
            $table->date('due_date'); // fecha de vencimiento de la cuota
            $table->decimal('amount', 10, 2); // monto de la cuota
            $table->string('status')->default('Pendiente'); // Pendiente | Pagada | Anulada
            $table->timestamp('paid_at')->nullable(); // fecha en la que se pagó

            $table->foreignId('sale_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
