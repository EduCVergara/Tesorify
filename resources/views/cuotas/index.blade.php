<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cuotas</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 p-4 text-sm text-green-700 sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('cuotas.generar') }}" class="grid gap-3 bg-white p-4 shadow-sm sm:rounded-lg md:grid-cols-5">
                @csrf
                <div>
                    <x-input-label for="anio" value="Anio" />
                    <x-text-input id="anio" name="anio" type="number" class="mt-1 block w-full" :value="old('anio', now()->year)" />
                    <x-input-error class="mt-2" :messages="$errors->get('anio')" />
                </div>
                <div>
                    <x-input-label for="mes" value="Mes" />
                    <select id="mes" name="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
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
                            <option value="{{ $numero }}" @selected((int) old('mes', now()->month) === $numero)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('mes')" />
                </div>
                <div>
                    <x-input-label for="monto" value="Monto" />
                    <x-text-input id="monto" name="monto" type="text" class="mt-1 block w-full" :value="old('monto', 5000)" data-money-input />
                    <x-input-error class="mt-2" :messages="$errors->get('monto')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="observaciones" value="Observaciones" />
                    <div class="mt-1 flex gap-3">
                        <x-text-input id="observaciones" name="observaciones" class="block w-full" :value="old('observaciones')" />
                        <x-primary-button>Generar</x-primary-button>
                    </div>
                </div>
            </form>

            <div class="bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" class="grid gap-3 md:grid-cols-5">
                    <x-text-input name="anio" type="number" :value="request('anio')" placeholder="Anio" />
                    <select name="mes" class="rounded-md border-gray-300 shadow-sm" data-tom-select>
                        <option value="">Todos los meses</option>
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
                            <option value="{{ $numero }}" @selected((string) request('mes') === (string) $numero)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <select name="estado" class="rounded-md border-gray-300 shadow-sm" data-tom-select>
                        <option value="">Todos los estados</option>
                        @foreach (['pendiente', 'pagada', 'parcial', 'eximida'] as $estado)
                            <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>
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

                <div class="mt-3 flex justify-end">
                    <a href="{{ route('cuotas.exportar', request()->query()) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Exportar filtro actual
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Periodo</th>
                            <th class="px-6 py-3">Socio</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Monto</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($cuotas as $cuota)
                            <tr>
                                <td class="px-6 py-4">{{ $cuota->mes }}/{{ $cuota->anio }}</td>
                                <td class="px-6 py-4"><a class="font-medium hover:underline" href="{{ route('socios.show', $cuota->socio) }}">{{ $cuota->socio->nombre }}</a></td>
                                <td class="px-6 py-4 capitalize">{{ $cuota->estado }}</td>
                                <td class="px-6 py-4 text-right">${{ number_format($cuota->monto, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if ($cuota->estado !== 'pagada')
                                        <form method="POST" action="{{ route('cuotas.pagar', $cuota) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="text-sm font-medium text-gray-900 hover:underline">Marcar pagada</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">Pagada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay cuotas para los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $cuotas->links() }}
        </div>
    </div>
</x-app-layout>
