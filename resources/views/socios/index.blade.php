<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Socios</h2>
            <a href="{{ route('socios.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Nuevo socio</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 p-4 text-sm text-green-700 sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <form method="GET" class="grid gap-3 bg-white p-4 shadow-sm sm:rounded-lg md:grid-cols-4">
                <x-text-input name="buscar" class="md:col-span-2" :value="request('buscar')" placeholder="Buscar nombre, codigo o casa" />
                <select name="estado" class="rounded-md border-gray-300 shadow-sm" data-tom-select>
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activo</option>
                    <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option>
                </select>
                <x-primary-button class="justify-center">Filtrar</x-primary-button>
            </form>

            <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">Codigo</th>
                            <th class="px-6 py-3">Casa</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Pendientes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($socios as $socio)
                            <tr>
                                <td class="px-6 py-4"><a class="font-medium text-gray-900 hover:underline" href="{{ route('socios.show', $socio) }}">{{ $socio->nombre }}</a></td>
                                <td class="px-6 py-4">{{ $socio->codigo_pago ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $socio->numero_casa ?? '-' }}</td>
                                <td class="px-6 py-4 capitalize">{{ $socio->estado }}</td>
                                <td class="px-6 py-4 text-right">{{ $socio->cuotas_pendientes_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay socios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $socios->links() }}
        </div>
    </div>
</x-app-layout>
