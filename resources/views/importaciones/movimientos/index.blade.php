<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Importar movimientos</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 p-4 text-sm text-green-700 sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('importaciones.movimientos.preview') }}" enctype="multipart/form-data" class="bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf
                <div>
                    <x-input-label for="archivo" value="Archivo Excel o CSV" />
                    <input id="archivo" name="archivo" type="file" accept=".xlsx,.xls,.csv,.txt" class="mt-1 block w-full rounded-md border border-gray-300 p-2 text-sm shadow-sm" required>
                    <x-input-error class="mt-2" :messages="$errors->get('archivo')" />
                </div>

                <div class="mt-5">
                    <x-primary-button>Previsualizar columnas</x-primary-button>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900">Ultimos movimientos importados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Descripcion</th>
                                <th class="px-6 py-3">Origen</th>
                                <th class="px-6 py-3 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($movimientosImportados as $movimiento)
                                <tr>
                                    <td class="px-6 py-4">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 capitalize">{{ $movimiento->tipo }}</td>
                                    <td class="px-6 py-4">{{ $movimiento->descripcion }}</td>
                                    <td class="px-6 py-4">{{ $movimiento->nombre_origen ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Todavia no hay movimientos importados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
