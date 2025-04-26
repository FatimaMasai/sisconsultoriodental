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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');
            $table->string('last_name_father');
            $table->string('last_name_mother');

            $table->string('identity_card')->unique();
            $table->string('birth_date');
            $table->string('gender');
            
            $table->string('phone');
            $table->string('email')->nullable();

            $table->string('address');

            $table->boolean('status')->default(true);

            // $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
