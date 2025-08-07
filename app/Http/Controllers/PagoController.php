<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->hasAnyRole(['Administrador', 'Pagos'])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $query = Pago::with(['factura.client', 'cliente', 'validador']);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_pago')) {
            $query->where('tipo_pago', $request->tipo_pago);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_transaccion', 'like', "%{$search}%")
                  ->orWhereHas('factura', function ($sq) use ($search) {
                      $sq->where('invoice_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('cliente', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $query->orderBy('created_at', 'desc');

        $pagos = $query->paginate(15)->withQueryString();

        // Estadísticas
        $stats = [
            'pendientes' => Pago::pendientes()->count(),
            'aprobados' => Pago::aprobados()->count(),
            'rechazados' => Pago::rechazados()->count(),
            'total' => Pago::count(),
            'monto_pendiente' => Pago::pendientes()->sum('monto'),
            'monto_aprobado' => Pago::aprobados()->sum('monto'),
        ];

        return view('pagos.index', compact('pagos', 'stats'));
    }

    public function show(Pago $pago)
    {
        // Verificar permisos
        if (!Auth::user()->hasAnyRole(['Administrador', 'Pagos'])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $pago->load(['factura.client', 'factura.user', 'factura.items.product', 'cliente', 'validador']);

        return view('pagos.show', compact('pago'));
    }

    public function aprobar(Request $request, Pago $pago)
    {
        // Verificar permisos
        if (!Auth::user()->hasAnyRole(['Administrador', 'Pagos'])) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        if (!$pago->canBeValidated()) {
            return back()->with('error', 'Este pago no puede ser validado.');
        }

        $request->validate([
            'observacion' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = [
                'estado' => $pago->estado,
                'validado_por' => $pago->validado_por,
                'validated_at' => $pago->validated_at,
            ];

            // Aprobar el pago
            $pago->aprobar(Auth::user(), $request->observacion);

            // Actualizar el estado de la factura a pagada
            $factura = $pago->factura;
            $factura->update([
                'status' => Invoice::STATUS_PAGADA,
                'payment_date' => now(),
                'paid_by' => $pago->pagado_por,
            ]);

            // Registrar auditoría
            AuditLog::create([
                'user_id' => $pago->pagado_por,
                'admin_id' => Auth::id(),
                'action' => 'approve_payment',
                'table_name' => 'pagos',
                'record_id' => $pago->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode([
                    'estado' => Pago::ESTADO_APROBADO,
                    'validado_por' => Auth::id(),
                    'validated_at' => now(),
                    'factura_status' => Invoice::STATUS_PAGADA,
                ]),
                'details' => json_encode([
                    'action' => 'Pago aprobado',
                    'pago_id' => $pago->id,
                    'factura_number' => $factura->invoice_number,
                    'monto' => $pago->monto,
                    'tipo_pago' => $pago->tipo_pago,
                    'approved_by' => Auth::user()->name,
                    'observacion' => $request->observacion,
                ]),
            ]);

            DB::commit();

            return redirect()->route('pagos.show', $pago)
                           ->with('success', 'Pago aprobado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al aprobar el pago: ' . $e->getMessage());
        }
    }

    public function rechazar(Request $request, Pago $pago)
    {
        // Verificar permisos
        if (!Auth::user()->hasAnyRole(['Administrador', 'Pagos'])) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        if (!$pago->canBeValidated()) {
            return back()->with('error', 'Este pago no puede ser validado.');
        }

        $request->validate([
            'observacion' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = [
                'estado' => $pago->estado,
                'validado_por' => $pago->validado_por,
                'validated_at' => $pago->validated_at,
            ];

            // Rechazar el pago
            $pago->rechazar(Auth::user(), $request->observacion);

            // Registrar auditoría
            AuditLog::create([
                'user_id' => $pago->pagado_por,
                'admin_id' => Auth::id(),
                'action' => 'reject_payment',
                'table_name' => 'pagos',
                'record_id' => $pago->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode([
                    'estado' => Pago::ESTADO_RECHAZADO,
                    'validado_por' => Auth::id(),
                    'validated_at' => now(),
                ]),
                'details' => json_encode([
                    'action' => 'Pago rechazado',
                    'pago_id' => $pago->id,
                    'factura_number' => $pago->factura->invoice_number,
                    'monto' => $pago->monto,
                    'tipo_pago' => $pago->tipo_pago,
                    'rejected_by' => Auth::user()->name,
                    'observacion' => $request->observacion,
                ]),
            ]);

            DB::commit();

            return redirect()->route('pagos.show', $pago)
                           ->with('success', 'Pago rechazado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al rechazar el pago: ' . $e->getMessage());
        }
    }

    public function estadisticas()
    {
        // Verificar permisos
        if (!Auth::user()->hasAnyRole(['Administrador', 'Pagos'])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $stats = [
            'resumen' => [
                'total_pagos' => Pago::count(),
                'pendientes' => Pago::pendientes()->count(),
                'aprobados' => Pago::aprobados()->count(),
                'rechazados' => Pago::rechazados()->count(),
            ],
            'montos' => [
                'total' => Pago::sum('monto'),
                'pendiente' => Pago::pendientes()->sum('monto'),
                'aprobado' => Pago::aprobados()->sum('monto'),
                'rechazado' => Pago::rechazados()->sum('monto'),
            ],
            'por_tipo' => Pago::selectRaw('tipo_pago, count(*) as cantidad, sum(monto) as total')
                              ->groupBy('tipo_pago')
                              ->get(),
            'ultimos_30_dias' => Pago::where('created_at', '>=', now()->subDays(30))
                                    ->selectRaw('DATE(created_at) as fecha, count(*) as cantidad, sum(monto) as total')
                                    ->groupBy('fecha')
                                    ->orderBy('fecha')
                                    ->get(),
        ];

        return view('pagos.estadisticas', compact('stats'));
    }
}
