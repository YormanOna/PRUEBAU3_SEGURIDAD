@echo off
echo === Prueba de API de Pagos ===

echo.
echo 1. Generando token para cliente...
curl -X POST http://localhost:8000/api/clients/tokens ^
-H "Content-Type: application/json" ^
-d "{\"email\":\"cliente@email.com\",\"password\":\"password\",\"token_name\":\"test_app\"}" ^
-w "Status: %%{http_code}\n"

echo.
echo.
echo 2. Para usar el token, ejecuta:
echo curl -X POST http://localhost:8000/api/pagos ^
echo -H "Authorization: Bearer TOKEN_AQUI" ^
echo -H "Content-Type: application/json" ^
echo -d "{\"invoice_id\":1,\"tipo_pago\":\"transferencia\",\"numero_transaccion\":\"TXN123456789\",\"monto\":5979.98,\"observaciones\":\"Pago de prueba\"}" ^
echo -w "Status: %%%%{http_code}\n"

pause
