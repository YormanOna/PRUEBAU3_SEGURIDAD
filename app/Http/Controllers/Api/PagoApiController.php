<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pago;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class PagoApiController extends Controller
{
    /**
     * Registrar pago de una factura
     */
    public function registerPayment(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado'
                ], 401);
            }

            // Validar datos del pago
            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required|integer|exists:invoices,id',
                'tipo_pago' => 'required|in:efectivo,tarjeta,transferencia,cheque',
                'numero_transaccion' => 'required|string|max:100',
                'monto' => 'required|numeric|min:0.01',
                'observaciones' => 'nullable|string|max:500'
            ], [
                'invoice_id.required' => 'El ID de la factura es obligatorio',
                'invoice_id.exists' => 'La factura especificada no existe',
                'tipo_pago.required' => 'El tipo de pago es obligatorio',
                'tipo_pago.in' => 'Tipo de pago no válido. Opciones: efectivo, tarjeta, transferencia, cheque',
                'numero_transaccion.required' => 'El número de transacción es obligatorio',
                'numero_transaccion.max' => 'El número de transacción no puede exceder 100 caracteres',
                'monto.required' => 'El monto es obligatorio',
                'monto.numeric' => 'El monto debe ser un número',
                'monto.min' => 'El monto debe ser mayor a 0',
                'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de pago no válidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar la factura
            $invoice = Invoice::find($request->invoice_id);
            
            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            // VALIDACIÓN DE SEGURIDAD: Verificar que la factura pertenezca al cliente autenticado
            if ($invoice->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para pagar esta factura. Solo puede pagar sus propias facturas.',
                    'error' => 'UNAUTHORIZED_INVOICE_ACCESS'
                ], 403); // 403 Forbidden - más apropiado para permisos
            }

            // Verificar que la factura puede ser pagada (debe estar pendiente)
            if ($invoice->status !== Invoice::STATUS_PENDIENTE) {
                $currentStatus = $this->getStatusText($invoice->status);
                return response()->json([
                    'success' => false,
                    'message' => 'La factura no está disponible para pago.',
                    'current_status' => $currentStatus,
                    'reason' => $invoice->status === Invoice::STATUS_PAGADA 
                        ? 'Esta factura ya ha sido pagada' 
                        : 'Esta factura ha sido anulada y no puede ser pagada'
                ], 400);
            }

            // Verificar si ya existe un pago pendiente o aprobado para esta factura
            $existingPayment = Pago::where('factura_id', $invoice->id)
                ->whereIn('estado', [Pago::ESTADO_PENDIENTE, Pago::ESTADO_APROBADO])
                ->first();

            if ($existingPayment) {
                $statusText = $existingPayment->estado === Pago::ESTADO_PENDIENTE ? 'pendiente de validación' : 'aprobado';
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un pago ' . $statusText . ' para esta factura',
                    'existing_payment' => [
                        'id' => $existingPayment->id,
                        'monto' => $existingPayment->monto,
                        'tipo_pago' => $existingPayment->tipo_pago,
                        'numero_transaccion' => $existingPayment->numero_transaccion,
                        'estado' => $existingPayment->estado,
                        'fecha' => $existingPayment->created_at->format('Y-m-d H:i:s')
                    ]
                ], 409); // 409 Conflict - más apropiado para duplicados
            }

            // Verificación adicional: evitar pagos duplicados con el mismo número de transacción
            $duplicateTransaction = Pago::where('numero_transaccion', $request->numero_transaccion)
                ->where('pagado_por', $user->id)
                ->whereIn('estado', [Pago::ESTADO_PENDIENTE, Pago::ESTADO_APROBADO])
                ->first();

            if ($duplicateTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un pago con el mismo número de transacción',
                    'suggestion' => 'Use un número de transacción único para cada pago'
                ], 409);
            }

            // Verificar que el monto coincida exactamente con el total de la factura
            if (abs($request->monto - $invoice->total) > 0.01) { // Tolerancia de 1 centavo para decimales
                return response()->json([
                    'success' => false,
                    'message' => 'El monto del pago debe ser exactamente igual al total de la factura',
                    'expected_amount' => $invoice->total,
                    'received_amount' => $request->monto,
                    'invoice_total' => 'S/ ' . number_format((float) $invoice->total, 2)
                ], 400);
            }

            // Crear el registro de pago
            $pago = Pago::create([
                'factura_id' => $invoice->id,
                'pagado_por' => $user->id,
                'monto' => $request->monto,
                'tipo_pago' => $request->tipo_pago,
                'numero_transaccion' => $request->numero_transaccion,
                'observacion' => $request->observaciones,
                'estado' => Pago::ESTADO_PENDIENTE,
            ]);

            // Registrar auditoría con información de seguridad
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'create_payment',
                'table_name' => 'pagos',
                'record_id' => $pago->id,
                'new_values' => json_encode([
                    'factura_id' => $invoice->id,
                    'factura_number' => $invoice->invoice_number,
                    'client_id' => $invoice->client_id, // Validar que coincida con el usuario autenticado
                    'monto' => $request->monto,
                    'tipo_pago' => $request->tipo_pago,
                    'numero_transaccion' => $request->numero_transaccion,
                    'estado' => Pago::ESTADO_PENDIENTE,
                ]),
                'details' => json_encode([
                    'action' => 'Pago registrado via API',
                    'client_id' => $user->id,
                    'client_name' => $user->name ?? 'Usuario API',
                    'invoice_belongs_to_client' => $invoice->client_id === $user->id, // Registro de seguridad
                    'factura_total' => $invoice->total,
                    'amount_matches_total' => abs($request->monto - $invoice->total) <= 0.01,
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip()
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente',
                'data' => [
                    'pago_id' => $pago->id,
                    'factura' => [
                        'id' => $invoice->id,
                        'numero' => $invoice->invoice_number,
                        'total' => $invoice->total
                    ],
                    'pago' => [
                        'monto' => $pago->monto,
                        'tipo_pago' => $pago->tipo_pago,
                        'numero_transaccion' => $pago->numero_transaccion,
                        'estado' => 'Pendiente de validación',
                        'fecha' => $pago->created_at->format('Y-m-d H:i:s')
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener texto del estado de factura
     */
    private function getStatusText(string $status): string
    {
        return match($status) {
            Invoice::STATUS_PENDIENTE => 'Pendiente',
            Invoice::STATUS_PAGADA => 'Pagada', 
            Invoice::STATUS_ANULADA => 'Anulada',
            default => ucfirst($status)
        };
    }
}
