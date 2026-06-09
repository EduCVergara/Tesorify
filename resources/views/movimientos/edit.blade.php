<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar movimiento</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto bg-white p-6 shadow-sm sm:rounded-lg sm:px-8">
            <form method="POST" action="{{ route('movimientos.update', $movimiento) }}">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" :value="old('fecha', $movimiento->fecha?->format('Y-m-d'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('fecha')" />
                    </div>

                    <div>
                        <x-input-label for="tipo" value="Tipo" />
                        <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            @foreach (['ingreso', 'egreso', 'ajuste'] as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo', $movimiento->tipo) === $tipo)>{{ ucfirst($tipo) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                    </div>

                    <div>
                        <x-input-label for="tipo_deposito" value="Tipo de deposito" />
                        <select id="tipo_deposito" name="tipo_deposito" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            <option value="">No aplica</option>
                            @foreach (['transferencia', 'efectivo', 'cheque'] as $tipoDeposito)
                                <option value="{{ $tipoDeposito }}" @selected(old('tipo_deposito', $movimiento->tipo_deposito) === $tipoDeposito)>{{ ucfirst($tipoDeposito) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('tipo_deposito')" />
                    </div>

                    <div>
                        <x-input-label for="categoria" value="Categoria" />
                        <select id="categoria" name="categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select data-tom-create="true">
                            <option value="">Sin categoria</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria }}" @selected(old('categoria', $movimiento->categoria) === $categoria)>{{ $categoria }}</option>
                            @endforeach
                            @if ($movimiento->categoria && ! in_array($movimiento->categoria, $categorias, true))
                                <option value="{{ $movimiento->categoria }}" selected>{{ $movimiento->categoria }}</option>
                            @endif
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('categoria')" />
                    </div>

                    <div>
                        <x-input-label for="monto" value="Monto" />
                        <x-text-input id="monto" name="monto" type="text" class="mt-1 block w-full" :value="old('monto', (int) $movimiento->monto)" data-money-input required />
                        <x-input-error class="mt-2" :messages="$errors->get('monto')" />
                    </div>

                    <div>
                        <x-input-label for="saldo" value="Saldo" />
                        <x-text-input id="saldo" name="saldo" type="text" class="mt-1 block w-full" :value="old('saldo', is_null($movimiento->saldo) ? null : (int) $movimiento->saldo)" data-money-input data-money-negative="true" />
                        <x-input-error class="mt-2" :messages="$errors->get('saldo')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="descripcion" value="Descripcion" />
                        <x-text-input id="descripcion" name="descripcion" class="mt-1 block w-full" :value="old('descripcion', $movimiento->descripcion)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
                    </div>

                    <div>
                        <x-input-label for="nombre_origen" value="Nombre origen" />
                        <x-text-input id="nombre_origen" name="nombre_origen" class="mt-1 block w-full" :value="old('nombre_origen', $movimiento->nombre_origen)" />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre_origen')" />
                    </div>

                    <div>
                        <x-input-label for="estado_conciliacion" value="Estado de conciliacion" />
                        <select id="estado_conciliacion" name="estado_conciliacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            @foreach (['pendiente', 'dudoso', 'conciliado'] as $estado)
                                <option value="{{ $estado }}" @selected(old('estado_conciliacion', $movimiento->estado_conciliacion) === $estado)>{{ ucfirst($estado) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('estado_conciliacion')" />
                    </div>

                    <div>
                        <x-input-label for="fuente" value="Fuente" />
                        <select id="fuente" name="fuente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            @foreach (['manual', 'mercado_pago', 'importacion_excel', 'ajuste'] as $fuente)
                                <option value="{{ $fuente }}" @selected(old('fuente', $movimiento->fuente) === $fuente)>{{ ucfirst(str_replace('_', ' ', $fuente)) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('fuente')" />
                    </div>

                    <div>
                        <x-input-label for="socio_id" value="Socio" />
                        <select id="socio_id" name="socio_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            <option value="">Sin socio</option>
                            @foreach ($socios as $socio)
                                <option value="{{ $socio->id }}" @selected((string) old('socio_id', $movimiento->socio_id) === (string) $socio->id)>
                                    {{ $socio->nombre }} @if ($socio->codigo_pago) - {{ $socio->codigo_pago }} @endif @if ($socio->numero_casa) - Casa {{ $socio->numero_casa }} @endif
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('socio_id')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="cuota_id" value="Nro de cuota" />
                        <select id="cuota_id" name="cuota_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            <option value="">Sin cuota asociada</option>
                            @foreach ($cuotas as $cuota)
                                <option value="{{ $cuota->id }}" @selected((string) old('cuota_id', $movimiento->cuota_id) === (string) $cuota->id)>
                                    #{{ $cuota->id }} - {{ $cuota->mes }}/{{ $cuota->anio }} - {{ $cuota->socio?->nombre }} - {{ ucfirst($cuota->estado) }} - ${{ number_format($cuota->monto, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('cuota_id')" />
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <x-primary-button>Guardar cambios</x-primary-button>
                    <a href="{{ route('movimientos.show', $movimiento) }}" class="text-sm">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
