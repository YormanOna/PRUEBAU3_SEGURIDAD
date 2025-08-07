<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle de Pago #') }}{{ $pago->id }}
            </h2>
            <a href="{{ route('pagos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Información del Pago -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">💳 Información del Pago</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Estado</label>
                                <div class="mt-1">
                                    @if($pago->estado == 'pendiente')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            ⏳ Pendiente
                                        </span>
                                    @elseif($pago->estado == 'aprobado')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                            ✅ Aprobado
                                        </span>
                                    @else
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                            ❌ Rechazado
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tipo de Pago</label>
                                <p class="mt-1 text-sm text-gray-900 capitalize">{{ $pago->tipo_pago }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Monto</label>
                                <p class="mt-1 text-lg font-bold text-gray-900">${{ number_format($pago->monto, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Fecha de Pago</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $pago->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @if($pago->numero_transaccion)
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-500">Número de Transacción</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 p-2 rounded">{{ $pago->numero_transaccion }}</p>
                            </div>
                            @endif
                            @if($pago->observacion)
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-500">Observaciones</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $pago->observacion }}</p>
                            </div>
                            @endif
                        </div>

                        @if($pago->validador)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-md font-medium text-gray-900 mb-3">👤 Información de Validación</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Validado por</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $pago->validador->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Fecha de Validación</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $pago->validated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Información de la Factura -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">🧾 Información de la Factura</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número de Factura</label>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ $pago->factura->invoice_number }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Estado de Factura</label>
                                <p class="mt-1 text-sm text-gray-900 capitalize">{{ $pago->factura->status }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Total Factura</label>
                                <p class="mt-1 text-lg font-bold text-gray-900">${{ number_format($pago->factura->total, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Fecha de Emisión</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $pago->factura->issue_date->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-md font-medium text-gray-900 mb-3">👤 Cliente</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <p class="text-sm"><span class="font-medium">Nombre:</span> {{ $pago->cliente->name }}</p>
                                <p class="text-sm"><span class="font-medium">Email:</span> {{ $pago->cliente->email }}</p>
                                @if($pago->cliente->phone)
                                <p class="text-sm"><span class="font-medium">Teléfono:</span> {{ $pago->cliente->phone }}</p>
                                @endif
                            </div>
                        </div>

                       
                    </div>
                </div>
            </div>

            <!-- Acciones de Validación -->
            @if($pago->canBeValidated() && Auth::user()->hasAnyRole(['Administrador', 'Pagos']))
            <div class="mt-6">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">⚡ Acciones de Validación</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Aprobar Pago -->
                            <form method="POST" action="{{ route('pagos.aprobar', $pago) }}" class="bg-green-50 border border-green-200 rounded-lg p-4">
                                @csrf
                                <h4 class="text-md font-medium text-green-800 mb-3">✅ Aprobar Pago</h4>
                                <div class="mb-4">
                                    <label for="observacion_aprobacion" class="block text-sm font-medium text-gray-700">Observaciones (opcional)</label>
                                    <textarea name="observacion" id="observacion_aprobacion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Comentarios adicionales sobre la aprobación..."></textarea>
                                </div>
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('¿Está seguro de aprobar este pago?')">
                                    ✅ Aprobar Pago
                                </button>
                            </form>

                            <!-- Rechazar Pago -->
                            <form method="POST" action="{{ route('pagos.rechazar', $pago) }}" class="bg-red-50 border border-red-200 rounded-lg p-4">
                                @csrf
                                <h4 class="text-md font-medium text-red-800 mb-3">❌ Rechazar Pago</h4>
                                <div class="mb-4">
                                    <label for="observacion_rechazo" class="block text-sm font-medium text-gray-700">Motivo del Rechazo *</label>
                                    <textarea name="observacion" id="observacion_rechazo" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Explicar el motivo del rechazo..." required></textarea>
                                </div>
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('¿Está seguro de rechazar este pago?')">
                                    ❌ Rechazar Pago
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Items de la Factura -->
            <div class="mt-6">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Items de la Factura</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pago->factura->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                            @if($item->product->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($item->product->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Subtotal:</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">${{ number_format($pago->factura->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">IVA:</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">${{ number_format($pago->factura->tax, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">${{ number_format($pago->factura->total, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
