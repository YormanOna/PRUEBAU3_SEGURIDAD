<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminar el constraint check existente
        DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check");
        
        // Luego actualizar los valores existentes
        DB::table('invoices')->where('status', 'active')->update(['status' => 'pendiente']);
        DB::table('invoices')->where('status', 'cancelled')->update(['status' => 'anulada']);
        DB::table('invoices')->where('status', 'paid')->update(['status' => 'pagada']);

        // Cambiar la columna para aceptar varchar temporalmente
        DB::statement("ALTER TABLE invoices ALTER COLUMN status DROP DEFAULT");
        DB::statement("ALTER TABLE invoices ALTER COLUMN status TYPE VARCHAR(20)");
        
        // Agregar el nuevo constraint
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('pendiente', 'pagada', 'anulada'))");
        
        // Establecer el nuevo valor por defecto
        DB::statement("ALTER TABLE invoices ALTER COLUMN status SET DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los valores
        DB::table('invoices')->where('status', 'pendiente')->update(['status' => 'active']);
        DB::table('invoices')->where('status', 'anulada')->update(['status' => 'cancelled']);
        DB::table('invoices')->where('status', 'pagada')->update(['status' => 'paid']);

        // Restaurar el constraint original
        DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check");
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('active', 'cancelled', 'paid'))");
        DB::statement("ALTER TABLE invoices ALTER COLUMN status SET DEFAULT 'active'");
    }
};
