<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard de tesoreria
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <script id="dashboard-chart-data" type="application/json">
                @json([
                    'flujoMensual' => $flujoMensual->values(),
                    'deudasPorSocio' => $deudasPorSocio->values(),
                ])
            </script>

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">Ingresos y egresos</h3>
                            <p class="mt-1 text-sm text-gray-500">Ultimos 6 meses</p>
                        </div>
                        <a href="{{ route('reportes.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">Reportes</a>
                    </div>
                    <div class="mt-6 h-80">
                        <canvas id="flujoMensualChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">Deudores y deudas</h3>
                            <p class="mt-1 text-sm text-gray-500">Mayores deudas activas</p>
                        </div>
                        <a href="{{ route('cuotas.index', ['estado' => 'pendiente']) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">Cuotas</a>
                    </div>
                    <div class="mt-6 h-80">
                        @if ($deudasPorSocio->isNotEmpty())
                            <canvas id="deudasSociosChart"></canvas>
                        @else
                            <div class="flex h-full items-center justify-center rounded-md border border-dashed border-gray-300 text-sm text-gray-500">
                                No hay deuda registrada.
                            </div>
                            <canvas id="deudasSociosChart" class="hidden"></canvas>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'Socios activos' => $sociosActivos,
                    'Recaudado del mes' => '$'.number_format($ingresosMes, 0, ',', '.'),
                    'Egresos del mes' => '$'.number_format($egresosMes, 0, ',', '.'),
                    'Saldo actual' => '$'.number_format($saldoActual, 0, ',', '.'),
                    'Cuotas pendientes' => $cuotasPendientes,
                    'Socios morosos' => $sociosMorosos,
                ] as $label => $value)
                    <div class="bg-white p-5 shadow-sm sm:rounded-lg">
                        <p class="text-sm text-gray-500">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <a href="{{ route('movimientos.create') }}" class="bg-gray-900 px-4 py-3 text-center text-sm font-semibold text-white sm:rounded-lg">Registrar pago</a>
                <a href="{{ route('importaciones.movimientos.index') }}" class="bg-white px-4 py-3 text-center text-sm font-semibold text-gray-800 shadow-sm sm:rounded-lg">Importar movimientos</a>
                <a href="{{ route('conciliaciones.index') }}" class="bg-white px-4 py-3 text-center text-sm font-semibold text-gray-800 shadow-sm sm:rounded-lg">Conciliar pagos</a>
                <a href="{{ route('cuotas.index', ['estado' => 'pendiente']) }}" class="bg-white px-4 py-3 text-center text-sm font-semibold text-gray-800 shadow-sm sm:rounded-lg">Ver pendientes</a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900">Ultimos movimientos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Descripcion</th>
                                <th class="px-6 py-3">Socio</th>
                                <th class="px-6 py-3 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($ultimosMovimientos as $movimiento)
                                <tr>
                                    <td class="px-6 py-4">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 capitalize">{{ $movimiento->tipo }}</td>
                                    <td class="px-6 py-4">{{ $movimiento->descripcion }}</td>
                                    <td class="px-6 py-4">{{ $movimiento->socio?->nombre ?? 'Sin conciliar' }}</td>
                                    <td class="px-6 py-4 text-right">${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Todavia no hay movimientos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
