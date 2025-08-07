<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Pagos') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('pagos.estadisticas') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    📊 Estadísticas
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Estadísticas de Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">⏳</div>
                        <div>
                            <div class="text-xl font-bold">{{ $stats['pendientes'] }}</div>
                            <div class="text-sm">Pendientes</div>
                            <div class="text-xs">${{ number_format($stats['monto_pendiente'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">✅</div>
                        <div>
                            <div class="text-xl font-bold">{{ $stats['aprobados'] }}</div>
                            <div class="text-sm">Aprobados</div>
                            <div class="text-xs">${{ number_format($stats['monto_aprobado'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">❌</div>
                        <div>
                            <div class="text-xl font-bold">{{ $stats['rechazados'] }}</div>
                            <div class="text-sm">Rechazados</div>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">💰</div>
                        <div>
                            <div class="text-xl font-bold">{{ $stats['total'] }}</div>
                            <div class="text-sm">Total Pagos</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('pagos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <select name="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="aprobado" {{ request('estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                <option value="rechazado" {{ request('estado') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de Pago</label>
                            <select name="tipo_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="efectivo" {{ request('tipo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="tarjeta" {{ request('tipo_pago') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                <option value="transferencia" {{ request('tipo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="cheque" {{ request('tipo_pago') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Número transacción, factura, cliente..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Filtrar
                            </button>
                            <a href="{{ route('pagos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Pagos -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pagos as $pago)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $pago->factura->invoice_number }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ${{ number_format($pago->factura->total, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $pago->cliente->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $pago->cliente->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($pago->tipo_pago) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pago->monto, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($pago->estado == 'pendiente')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                ⏳ Pendiente
                                            </span>
                                        @elseif($pago->estado == 'aprobado')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                ✅ Aprobado
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                ❌ Rechazado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $pago->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('pagos.show', $pago) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No se encontraron pagos.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $pagos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
