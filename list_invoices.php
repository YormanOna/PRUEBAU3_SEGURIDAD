<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Todas las Facturas Disponibles ===" . PHP_EOL;

$invoices = \App\Models\Invoice::where('status', 'pendiente')->get();
foreach ($invoices as $invoice) {
    echo "ID: " . $invoice->id . " | Número: " . $invoice->invoice_number . " | Total: S/ " . number_format($invoice->total, 2) . " | Cliente ID: " . $invoice->client_id . PHP_EOL;
}
?>
