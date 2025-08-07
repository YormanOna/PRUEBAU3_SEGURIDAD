<?php
// Script de prueba para la API de pagos de clientes

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

echo "=== PRUEBA API DE PAGOS - CLIENTES ===\n\n";

// Configuración
$baseUrl = 'http://localhost:8000/api';
$token = ''; // Se necesita generar un token para el cliente

// Primero necesitamos generar un token para un cliente
echo "1. Para usar esta API, primero necesitas:\n";
echo "   - Tener un cliente registrado en el sistema\n";
echo "   - Generar un token de acceso para ese cliente\n";
echo "   - Usar el token en las solicitudes API\n\n";

echo "2. Endpoints disponibles:\n";
echo "   GET  /api/client/invoices - Obtener facturas del cliente\n";
echo "   GET  /api/client/invoices/{id} - Obtener una factura específica\n";
echo "   POST /api/client/invoices/{id}/pay - Registrar pago de factura\n";
echo "   GET  /api/client/payments - Historial de pagos del cliente\n";
echo "   GET  /api/client/payments/{id} - Obtener un pago específico\n\n";

echo "3. Ejemplo de uso con cURL:\n\n";

echo "# Obtener facturas del cliente:\n";
echo "curl -X GET '{$baseUrl}/client/invoices' \\\n";
echo "     -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\\n";
echo "     -H 'Accept: application/json'\n\n";

echo "# Registrar un pago:\n";
echo "curl -X POST '{$baseUrl}/client/invoices/1/pay' \\\n";
echo "     -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -H 'Accept: application/json' \\\n";
echo "     -d '{\n";
echo "       \"tipo_pago\": \"transferencia\",\n";
echo "       \"numero_transaccion\": \"TXN-12345\",\n";
echo "       \"monto\": 150.50,\n";
echo "       \"observaciones\": \"Pago mediante transferencia bancaria\"\n";
echo "     }'\n\n";

echo "4. Estructura de respuesta exitosa para registro de pago:\n";
echo "{\n";
echo "  \"success\": true,\n";
echo "  \"message\": \"Pago registrado exitosamente. Será validado por el administrador.\",\n";
echo "  \"data\": {\n";
echo "    \"payment_id\": 1,\n";
echo "    \"invoice_number\": \"INV-000001\",\n";
echo "    \"amount\": 150.50,\n";
echo "    \"payment_method\": \"transferencia\",\n";
echo "    \"transaction_number\": \"TXN-12345\",\n";
echo "    \"status\": \"pendiente\",\n";
echo "    \"status_text\": \"Pendiente de validación\",\n";
echo "    \"created_at\": \"2025-08-07 10:30:00\"\n";
echo "  }\n";
echo "}\n\n";

echo "5. Tipos de pago válidos:\n";
echo "   - efectivo\n";
echo "   - tarjeta\n";
echo "   - transferencia\n";
echo "   - cheque\n\n";

echo "6. Estados del pago:\n";
echo "   - pendiente: Esperando validación del administrador\n";
echo "   - aprobado: Pago validado y aceptado\n";
echo "   - rechazado: Pago rechazado por el administrador\n\n";

echo "Para generar un token para un cliente, usa la interfaz web del sistema.\n";
