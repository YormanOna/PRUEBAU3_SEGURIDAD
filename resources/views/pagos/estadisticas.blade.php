<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Estadísticas de Pagos') }}
            </h2>
            <a href="{{ route('pagos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Volver a Pagos
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Resumen General -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-3xl mr-3">💰</div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['resumen']['total_pagos'] }}</div>
                            <div class="text-sm">Total Pagos</div>
                            <div class="text-xs">${{ number_format($stats['montos']['total'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-3xl mr-3">⏳</div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['resumen']['pendientes'] }}</div>
                            <div class="text-sm">Pendientes</div>
                            <div class="text-xs">${{ number_format($stats['montos']['pendiente'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-3xl mr-3">✅</div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['resumen']['aprobados'] }}</div>
                            <div class="text-sm">Aprobados</div>
                            <div class="text-xs">${{ number_format($stats['montos']['aprobado'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="text-3xl mr-3">❌</div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['resumen']['rechazados'] }}</div>
                            <div class="text-sm">Rechazados</div>
                            <div class="text-xs">${{ number_format($stats['montos']['rechazado'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Estadísticas por Tipo de Pago -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">📊 Pagos por Tipo</h3>
                        
                        <div class="space-y-4">
                            @php
                                $tipos = [
                                    'efectivo' => ['icon' => '💵', 'name' => 'Efectivo', 'color' => 'green'],
                                    'tarjeta' => ['icon' => '💳', 'name' => 'Tarjeta', 'color' => 'blue'],
                                    'transferencia' => ['icon' => '🏦', 'name' => 'Transferencia', 'color' => 'purple'],
                                    'cheque' => ['icon' => '📄', 'name' => 'Cheque', 'color' => 'orange']
                                ];
                            @endphp

                            @foreach($stats['por_tipo'] as $tipo)
                                @php
                                    $tipoInfo = $tipos[$tipo->tipo_pago] ?? ['icon' => '💰', 'name' => $tipo->tipo_pago, 'color' => 'gray'];
                                    $porcentaje = $stats['resumen']['total_pagos'] > 0 ? ($tipo->cantidad / $stats['resumen']['total_pagos']) * 100 : 0;
                                @endphp
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="text-2xl mr-3">{{ $tipoInfo['icon'] }}</div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $tipoInfo['name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $tipo->cantidad }} pagos</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-gray-900">${{ number_format($tipo->total, 2) }}</div>
                                        <div class="text-sm text-gray-500">{{ number_format($porcentaje, 1) }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Resumen de Montos -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">💰 Resumen de Montos</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center">
                                    <div class="text-2xl mr-3">💎</div>
                                    <div class="font-medium text-blue-900">Total Procesado</div>
                                </div>
                                <div class="text-xl font-bold text-blue-900">${{ number_format($stats['montos']['total'], 2) }}</div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="flex items-center">
                                    <div class="text-2xl mr-3">⏳</div>
                                    <div class="font-medium text-yellow-900">En Proceso</div>
                                </div>
                                <div class="text-xl font-bold text-yellow-900">${{ number_format($stats['montos']['pendiente'], 2) }}</div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center">
                                    <div class="text-2xl mr-3">✅</div>
                                    <div class="font-medium text-green-900">Aprobados</div>
                                </div>
                                <div class="text-xl font-bold text-green-900">${{ number_format($stats['montos']['aprobado'], 2) }}</div>
                            </div>

                            @if($stats['montos']['rechazado'] > 0)
                            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="flex items-center">
                                    <div class="text-2xl mr-3">❌</div>
                                    <div class="font-medium text-red-900">Rechazados</div>
                                </div>
                                <div class="text-xl font-bold text-red-900">${{ number_format($stats['montos']['rechazado'], 2) }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividad de los Últimos 30 Días -->
            @if($stats['ultimos_30_dias']->isNotEmpty())
            <div class="mt-6">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">📈 Actividad - Últimos 30 Días</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($stats['ultimos_30_dias'] as $dia)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($dia->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $dia->cantidad }} pagos
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($dia->total, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ${{ number_format($dia->total / $dia->cantidad, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
