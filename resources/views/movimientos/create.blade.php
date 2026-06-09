<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registrar movimiento</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto bg-white p-6 shadow-sm sm:rounded-lg sm:px-8">
            <form method="POST" action="{{ route('movimientos.store') }}">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" :value="old('fecha', $movimiento->fecha->format('Y-m-d'))" />
                    </div>
                    <div>
                        <x-input-label for="tipo" value="Tipo" />
                        <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            @foreach (['ingreso', 'egreso', 'ajuste'] as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo', $movimiento->tipo) === $tipo)>{{ ucfirst($tipo) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="tipo_deposito" value="Tipo de deposito" />
                        <select id="tipo_deposito" name="tipo_deposito" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            <option value="">No aplica</option>
                            @foreach (['transferencia', 'efectivo', 'cheque'] as $tipoDeposito)
                                <option value="{{ $tipoDeposito }}" @selected(old('tipo_deposito') === $tipoDeposito)>{{ ucfirst($tipoDeposito) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('tipo_deposito')" />
                    </div>
                    <div>
                        <x-input-label for="categoria" value="Categoria" />
                        <select id="categoria" name="categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select data-tom-create="true">
                            <option value="">Sin categoria</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria }}" @selected(old('categoria') === $categoria)>{{ $categoria }}</option>
                            @endforeach
                            @if (old('categoria') && ! in_array(old('categoria'), $categorias, true))
                                <option value="{{ old('categoria') }}" selected>{{ old('categoria') }}</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <x-input-label for="monto" value="Monto" />
                        <x-text-input id="monto" name="monto" type="text" class="mt-1 block w-full" :value="old('monto')" data-money-input />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="descripcion" value="Descripcion" />
                        <x-text-input id="descripcion" name="descripcion" class="mt-1 block w-full" :value="old('descripcion')" />
                    </div>
                    <div>
                        <x-input-label for="nombre_origen" value="Nombre origen" />
                        <x-text-input id="nombre_origen" name="nombre_origen" class="mt-1 block w-full" :value="old('nombre_origen')" />
                    </div>
                    <div>
                        <x-input-label for="socio_id" value="Socio relacionado" />
                        <select id="socio_id" name="socio_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                            <option value="">Sin conciliar</option>
                            @foreach ($socios as $socio)
                                <option value="{{ $socio->id }}" @selected(old('socio_id') == $socio->id)>{{ $socio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('movimientos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
