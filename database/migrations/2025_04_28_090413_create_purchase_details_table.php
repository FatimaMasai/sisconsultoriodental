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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();

            $table->integer('quantity'); //cantidad
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2); //total por producto

            $table->foreignId('purchase_id')->constrained()->onDelete('cascade'); // Relación con la tabla compra
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Relación con la tabla producto


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
