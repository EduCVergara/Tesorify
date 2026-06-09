<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Libro banco</h2>
            <a href="{{ route('movimientos.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Nuevo movimiento</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 p-4 text-sm text-green-700 sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <form method="GET" class="grid gap-3 bg-white p-4 shadow-sm sm:rounded-lg md:grid-cols-7">
                <x-text-input name="desde" type="date" :value="request('desde')" />
                <x-text-input name="hasta" type="date" :value="request('hasta')" />
                <select name="tipo" class="rounded-md border-gray-300 shadow-sm" data-tom-select>
                    <option value="">Todos los tipos</option>
                    @foreach (['ingreso', 'egreso', 'ajuste'] as $tipo)
                        <option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>{{ ucfirst($tipo) }}</option>
                    @endforeach
                </select>
                <select name="tipo_deposito" class="rounded-md border-gray-300 shadow-sm" data-tom-select>
                    <option value="">Todos los depositos</option>
                    @foreach (['transferencia', 'efectivo', 'cheque'] as $tipoDeposito)
                        <option value="{{ $tipoDeposito }}" @selected(request('tipo_deposito') === $tipoDeposito)>{{ ucfirst($tipoDeposito) }}</option>
                    @endforeach
                </select>
                <select name="estado_conciliacion" class="rounded-md border-gray-300 shadow-sm" data-tom-select>
                    <option value="">Conciliacion</option>
                    @foreach (['conciliado', 'pendiente', 'dudoso'] as $estado)
                        <option value="{{ $estado }}" @selected(request('estado_conciliacion') === $estado)>{{ ucfirst($estado) }}</option>
                    @endforeach
                </select>
                <select name="socio_id" class="rounded-md border-gray-300 shadow-sm" data-tom-select>
                    <option value="">Todos los socios</option>
                    @foreach ($socios as $socio)
                        <option value="{{ $socio->id }}" @selected((string) request('socio_id') === (string) $socio->id)>
                            {{ $socio->nombre }} @if ($socio->codigo_pago) - {{ $socio->codigo_pago }} @endif @if ($socio->numero_casa) - Casa {{ $socio->numero_casa }} @endif
                        </option>
                    @endforeach
                </select>
                <x-primary-button class="justify-center">Filtrar</x-primary-button>
            </form>

            <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3">Tipo</th>
                            <th class="px-6 py-3">Deposito</th>
                            <th class="px-6 py-3">Descripcion</th>
                            <th class="px-6 py-3">Origen</th>
                            <th class="px-6 py-3">Conciliacion</th>
                            <th class="px-6 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($movimientos as $movimiento)
                            <tr>
                                <td class="px-6 py-4">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 capitalize">{{ $movimiento->tipo }}</td>
                                <td class="px-6 py-4 capitalize">{{ $movimiento->tipo_deposito ?? '-' }}</td>
                                <td class="px-6 py-4"><a class="font-medium hover:underline" href="{{ route('movimientos.show', $movimiento) }}">{{ $movimiento->descripcion }}</a></td>
                                <td class="px-6 py-4">{{ $movimiento->nombre_origen ?? $movimiento->socio?->nombre ?? '-' }}</td>
                                <td class="px-6 py-4 capitalize">{{ $movimiento->estado_conciliacion }}</td>
                                <td class="px-6 py-4 text-right">${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No hay movimientos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $movimientos->links() }}
        </div>
    </div>
</x-app-layout>
