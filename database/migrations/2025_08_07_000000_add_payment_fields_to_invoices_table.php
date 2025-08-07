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
        Schema::table('invoices', function (Blueprint $table) {
            // Campos para el manejo de pagos
            $table->timestamp('payment_date')->nullable()->after('status');
            $table->unsignedBigInteger('paid_by')->nullable()->after('payment_date');
            
            // Crear la foreign key constraint para paid_by
            $table->foreign('paid_by')->references('id')->on('clients')->onDelete('set null');
            
            // Agregar índice para mejorar performance en consultas
            $table->index(['status', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['paid_by']);
            
            // Eliminar índice
            $table->dropIndex(['status', 'payment_date']);
            
            // Eliminar las columnas
            $table->dropColumn(['payment_date', 'paid_by']);
        });
    }
};
