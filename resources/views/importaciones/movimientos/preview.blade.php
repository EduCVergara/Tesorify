<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mapear columnas</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('importaciones.movimientos.store') }}" class="bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf
                <input type="hidden" name="archivo_path" value="{{ $archivoPath }}">
                <input type="hidden" name="archivo_nombre" value="{{ $archivoNombre }}">

                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <x-input-label for="fila_inicio" value="Primera fila de datos" />
                        <x-text-input id="fila_inicio" name="fila_inicio" type="number" min="1" class="mt-1 block w-full" :value="old('fila_inicio', $filaInicio)" />
                    </div>

                    @foreach ([
                        'fecha_col' => 'Fecha',
                        'descripcion_col' => 'Descripcion',
                        'nombre_origen_col' => 'Nombre origen',
                        'monto_col' => 'Monto',
                        'tipo_col' => 'Tipo movimiento',
                        'categoria_col' => 'Categoria',
                        'saldo_col' => 'Saldo',
                    ] as $field => $label)
                        <div>
                            <x-input-label :for="$field" :value="$label" />
                            <select id="{{ $field }}" name="{{ $field }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
                                @if (! in_array($field, ['fecha_col', 'descripcion_col', 'monto_col'], true))
                                    <option value="">No mapear</option>
                                @endif
                                @foreach ($columnas as $columna => $descripcion)
                                    <option value="{{ $columna }}" @selected(old($field) === $columna)>{{ $descripcion }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get($field)" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <x-primary-button>Importar movimientos</x-primary-button>
                    <a href="{{ route('importaciones.movimientos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900">Previsualizacion de {{ $archivoNombre }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($filas as $numero => $fila)
                                <tr>
                                    <td class="whitespace-nowrap bg-gray-50 px-3 py-2 font-semibold text-gray-500">{{ $numero }}</td>
                                    @foreach ($fila as $columna => $valor)
                                        <td class="whitespace-nowrap px-3 py-2">
                                            <span class="font-semibold text-gray-400">{{ $columna }}</span>
                                            {{ $valor }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
