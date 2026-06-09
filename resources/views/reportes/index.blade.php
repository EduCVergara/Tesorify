<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reportes</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 lg:grid-cols-2">
                <form method="GET" action="{{ route('reportes.descargar', 'libro-banco') }}" class="bg-white p-5 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">Libro banco</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <x-input-label for="libro_desde" value="Desde" />
                            <x-text-input id="libro_desde" name="desde" type="date" class="mt-1 block w-full" :value="$inicioMes" />
                        </div>
                        <div>
                            <x-input-label for="libro_hasta" value="Hasta" />
                            <x-text-input id="libro_hasta" name="hasta" type="date" class="mt-1 block w-full" :value="$finMes" />
                        </div>
                    </div>
                    <x-primary-button class="mt-4">Descargar</x-primary-button>
                </form>

                <form method="GET" action="{{ route('reportes.descargar', 'ingresos-gastos-rango') }}" class="bg-white p-5 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">Ingresos y gastos</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <x-input-label for="flujo_desde" value="Desde" />
                            <x-text-input id="flujo_desde" name="desde" type="date" class="mt-1 block w-full" :value="$inicioMes" />
                        </div>
                        <div>
                            <x-input-label for="flujo_hasta" value="Hasta" />
                            <x-text-input id="flujo_hasta" name="hasta" type="date" class="mt-1 block w-full" :value="$finMes" />
                        </div>
                    </div>
                    <x-primary-button class="mt-4">Descargar</x-primary-button>
                </form>

                @foreach ([
                    'nomina-cuotas' => 'Nomina de cuotas',
                    'deuda-cuotas' => 'Deuda de cuotas',
                    'gastos-mes' => 'Detalle de gastos del mes',
                ] as $reporte => $titulo)
                    <form method="GET" action="{{ route('reportes.descargar', $reporte) }}" class="bg-white p-5 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold text-gray-900">{{ $titulo }}</h3>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div>
                                <x-input-label :for="$reporte.'_anio'" value="Anio" />
                                <select id="{{ $reporte.'_anio' }}" name="anio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                                    @foreach (range(2025, $anioActual + 1) as $anio)
                                        <option value="{{ $anio }}" @selected($anio === $anioActual)>{{ $anio }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label :for="$reporte.'_mes'" value="Mes" />
                                <select id="{{ $reporte.'_mes' }}" name="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                                    @foreach ([
                                        1 => 'Enero',
                                        2 => 'Febrero',
                                        3 => 'Marzo',
                                        4 => 'Abril',
                                        5 => 'Mayo',
                                        6 => 'Junio',
                                        7 => 'Julio',
                                        8 => 'Agosto',
                                        9 => 'Septiembre',
                                        10 => 'Octubre',
                                        11 => 'Noviembre',
                                        12 => 'Diciembre',
                                    ] as $numero => $nombre)
                                        <option value="{{ $numero }}" @selected($numero === $mesActual)>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <x-primary-button class="mt-4">Descargar</x-primary-button>
                    </form>
                @endforeach

                <form method="GET" action="{{ route('reportes.descargar', 'deudores') }}" class="bg-white p-5 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">Deudores</h3>
                    <div class="mt-4 text-sm text-gray-600">Socios activos con cuotas pendientes o parciales.</div>
                    <x-primary-button class="mt-4">Descargar</x-primary-button>
                </form>

                <form method="GET" action="{{ route('reportes.descargar', 'sector') }}" class="bg-white p-5 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">Reporte por sector</h3>
                    <div class="mt-4">
                        <x-input-label for="sector" value="Sector" />
                        <select id="sector" name="sector" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            <option value="">Todos los sectores</option>
                            @foreach ($sectores as $sector)
                                <option value="{{ $sector }}">{{ $sector }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button class="mt-4">Descargar</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
