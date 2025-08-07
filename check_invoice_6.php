<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Detalles de la Factura 6 ===" . PHP_EOL;

$invoice = \App\Models\Invoice::find(6);
if ($invoice) {
    echo "ID: " . $invoice->id . PHP_EOL;
    echo "Número: " . $invoice->invoice_number . PHP_EOL;
    echo "Subtotal: S/ " . number_format($invoice->subtotal, 2) . PHP_EOL;
    echo "Impuesto: S/ " . number_format($invoice->tax, 2) . PHP_EOL;
    echo "Total: S/ " . number_format($invoice->total, 2) . PHP_EOL;
    echo "Estado: " . $invoice->status . PHP_EOL;
    echo "Cliente ID: " . $invoice->client_id . PHP_EOL;
} else {
    echo "Factura 6 no encontrada" . PHP_EOL;
}
?>
