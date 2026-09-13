<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Segunda tanda de campos de personalización de Configuración >
     * Apariencia, a pedido del cliente: tamaño de letra global, nombre del
     * sistema (título de la pestaña del navegador) y datos de contacto de
     * la clínica para imprimir en recetas/comprobantes sin tocar código.
     *
     * Todos nullable/con default para no romper la fila única existente
     * (id = 1) que ya tienen los clientes que instalaron antes de este
     * cambio.
     */
    public function up(): void
    {
        Schema::table('clinic_settings', function (Blueprint $table) {
            $table->string('font_size', 10)->default('normal')->after('primary_color');
            $table->string('system_name')->nullable()->after('font_size');
            $table->string('contact_address')->nullable()->after('system_name');
            $table->string('contact_phone', 40)->nullable()->after('contact_address');
            $table->string('contact_social')->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_settings', function (Blueprint $table) {
            $table->dropColumn([
                'font_size',
                'system_name',
                'contact_address',
                'contact_phone',
                'contact_social',
            ]);
        });
    }
};
