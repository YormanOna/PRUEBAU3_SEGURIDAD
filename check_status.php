<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificando estados de facturas ===" . PHP_EOL;

$invoices = App\Models\Invoice::select('id', 'invoice_number', 'status')->take(5)->get();

foreach ($invoices as $invoice) {
    echo "ID: {$invoice->id}, Número: {$invoice->invoice_number}, Estado: {$invoice->status}" . PHP_EOL;
}

echo PHP_EOL . "=== Constantes del modelo Invoice ===" . PHP_EOL;
echo "STATUS_PENDIENTE: " . App\Models\Invoice::STATUS_PENDIENTE . PHP_EOL;
echo "STATUS_PAGADA: " . App\Models\Invoice::STATUS_PAGADA . PHP_EOL;
echo "STATUS_ANULADA: " . App\Models\Invoice::STATUS_ANULADA . PHP_EOL;
