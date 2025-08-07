<?php

require 'vendor/autoload.php';

// Inicializar Laravel
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Client;
use App\Models\Invoice;

echo "=== PRUEBA REAL API DE PAGOS ===\n\n";

try {
    // 1. Obtener un cliente existente
    $client = Client::first();
    if (!$client) {
        echo "❌ No hay clientes registrados. Crea un cliente primero.\n";
        exit(1);
    }
    
    echo "✅ Cliente encontrado: {$client->name} (ID: {$client->id})\n";
    
    // 2. Generar token para el cliente
    $token = $client->createToken('API Test Token');
    echo "✅ Token generado exitosamente\n";
    echo "Token: {$token->plainTextToken}\n\n";
    
    // 3. Verificar facturas del cliente
    $facturas = Invoice::where('client_id', $client->id)->get();
    echo "📊 Facturas del cliente: {$facturas->count()}\n";
    
    foreach ($facturas as $factura) {
        echo "  - {$factura->invoice_number}: S/ {$factura->total} ({$factura->status})\n";
    }
    
    if ($facturas->count() > 0) {
        echo "\n✅ Sistema listo para probar la API\n";
        echo "\n📝 Para probar con cURL, usa el siguiente comando:\n\n";
        
        $factura = $facturas->first();
        $curlCommand = "curl -X POST 'http://localhost:8000/api/client/invoices/{$factura->id}/pay' \\
     -H 'Authorization: Bearer {$token->plainTextToken}' \\
     -H 'Content-Type: application/json' \\
     -H 'Accept: application/json' \\
     -d '{
       \"tipo_pago\": \"transferencia\",
       \"numero_transaccion\": \"TEST-" . time() . "\",
       \"monto\": " . $factura->total . ",
       \"observaciones\": \"Pago de prueba via API\"
     }'";
        
        echo $curlCommand . "\n\n";
        echo "O para obtener las facturas del cliente:\n\n";
        echo "curl -X GET 'http://localhost:8000/api/client/invoices' \\
     -H 'Authorization: Bearer {$token->plainTextToken}' \\
     -H 'Accept: application/json'\n\n";
    } else {
        echo "\n⚠️  No hay facturas para este cliente. Crea una factura primero.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✨ Prueba completada\n";
