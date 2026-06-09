<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle de movimiento</h2>
            <a href="{{ route('movimientos.edit', $movimiento) }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Editar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 p-4 text-sm text-green-700 sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-4 md:grid-cols-2">
                    <div><dt class="text-sm text-gray-500">Fecha</dt><dd>{{ $movimiento->fecha->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Tipo</dt><dd class="capitalize">{{ $movimiento->tipo }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Tipo de deposito</dt><dd class="capitalize">{{ $movimiento->tipo_deposito ?? 'No aplica' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Monto</dt><dd>${{ number_format($movimiento->monto, 0, ',', '.') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Estado</dt><dd class="capitalize">{{ $movimiento->estado_conciliacion }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-sm text-gray-500">Descripcion</dt><dd>{{ $movimiento->descripcion }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Origen</dt><dd>{{ $movimiento->nombre_origen ?? '-' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Socio</dt><dd>{{ $movimiento->socio?->nombre ?? 'Sin conciliar' }}</dd></div>
                    <div>
                        <dt class="text-sm text-gray-500">Nro de cuota</dt>
                        <dd>
                            @if ($movimiento->cuota)
                                #{{ $movimiento->cuota->id }} - {{ $movimiento->cuota->mes }}/{{ $movimiento->cuota->anio }} - {{ $movimiento->cuota->socio?->nombre }}
                            @else
                                Sin cuota asociada
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-sm text-gray-500">Fuente</dt><dd class="capitalize">{{ str_replace('_', ' ', $movimiento->fuente) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
