<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conciliar pagos</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 p-4 text-sm text-green-700 sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3">Origen</th>
                            <th class="px-6 py-3">Descripcion</th>
                            <th class="px-6 py-3">Sugerencia</th>
                            <th class="px-6 py-3 text-right">Monto</th>
                            <th class="px-6 py-3 text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($movimientos as $item)
                            @php
                                $movimiento = $item['movimiento'];
                                $sugerencia = $item['sugerencia'];
                            @endphp
                            <tr>
                                <td class="px-6 py-4">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">{{ $movimiento->nombre_origen ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $movimiento->descripcion }}</td>
                                <td class="px-6 py-4">
                                    @if ($sugerencia)
                                        <div class="font-medium text-gray-900">{{ $sugerencia['socio']->nombre }}</div>
                                        <div class="text-xs text-gray-500">Coincidencia {{ $sugerencia['nivel'] }} · {{ $sugerencia['puntaje'] }}%</div>
                                    @else
                                        <span class="text-gray-500">Sin sugerencia</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('conciliaciones.show', $movimiento) }}" class="font-medium text-gray-900 hover:underline">Revisar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No hay ingresos pendientes de conciliacion.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $movimientos->links() }}
        </div>
    </div>
</x-app-layout>
