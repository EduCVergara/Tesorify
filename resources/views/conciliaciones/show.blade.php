<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Confirmar conciliacion</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">Movimiento</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-gray-500">Fecha</dt><dd>{{ $movimiento->fecha->format('d/m/Y') }}</dd></div>
                        <div><dt class="text-gray-500">Origen</dt><dd>{{ $movimiento->nombre_origen ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Descripcion</dt><dd>{{ $movimiento->descripcion }}</dd></div>
                        <div><dt class="text-gray-500">Monto</dt><dd class="font-semibold">${{ number_format($movimiento->monto, 0, ',', '.') }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <h3 class="font-semibold text-gray-900">Sugerencias</h3>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @forelse ($sugerencias as $sugerencia)
                            <a href="{{ route('conciliaciones.show', [$movimiento, 'socio_id' => $sugerencia['socio']->id]) }}" class="rounded-md border border-gray-200 p-4 hover:border-gray-400">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $sugerencia['socio']->nombre }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $sugerencia['socio']->codigo_pago ?? 'Sin codigo' }}</div>
                                    </div>
                                    <span class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ $sugerencia['puntaje'] }}%</span>
                                </div>
                                <div class="mt-3 text-xs text-gray-500">{{ implode(', ', $sugerencia['razones']) }}</div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">No se encontraron sugerencias automaticas.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('conciliaciones.confirmar', $movimiento) }}" class="bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <x-input-label for="socio_id" value="Socio" />
                        <select id="socio_id" name="socio_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select onchange="window.location='{{ route('conciliaciones.show', $movimiento) }}?socio_id='+this.value">
                            <option value="">Seleccionar socio</option>
                            @foreach ($socios as $socio)
                                <option value="{{ $socio->id }}" @selected((int) old('socio_id', $socioSeleccionado?->id) === $socio->id)>
                                    {{ $socio->nombre }} @if ($socio->codigo_pago) - {{ $socio->codigo_pago }} @endif @if ($socio->numero_casa) - Casa {{ $socio->numero_casa }} @endif
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('socio_id')" />
                    </div>

                    <div>
                        <x-input-label for="observaciones" value="Observacion" />
                        <x-text-input id="observaciones" name="observaciones" class="mt-1 block w-full" :value="old('observaciones', 'Conciliada manualmente.')" />
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="font-semibold text-gray-900">Cuotas pendientes del socio</h3>
                    <div class="mt-3 overflow-x-auto rounded-md border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Pagar</th>
                                    <th class="px-4 py-3">Periodo</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3 text-right">Monto</th>
                                    <th class="px-4 py-3 text-right">Pagado</th>
                                    <th class="px-4 py-3 text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($cuotasPendientes as $cuota)
                                    @php
                                        $saldo = max((float) $cuota->monto - (float) $cuota->monto_pagado, 0);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="cuota_ids[]" value="{{ $cuota->id }}" class="rounded border-gray-300" @checked(in_array($cuota->id, old('cuota_ids', [])))>
                                        </td>
                                        <td class="px-4 py-3">{{ $cuota->mes }}/{{ $cuota->anio }}</td>
                                        <td class="px-4 py-3 capitalize">{{ $cuota->estado }}</td>
                                        <td class="px-4 py-3 text-right">${{ number_format($cuota->monto, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right">${{ number_format($cuota->monto_pagado, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right">${{ number_format($saldo, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Selecciona un socio o no hay cuotas pendientes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <x-primary-button>Confirmar conciliacion</x-primary-button>
                    <a href="{{ route('conciliaciones.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Volver</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
