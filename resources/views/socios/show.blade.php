<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $socio->nombre }}</h2>
            <a href="{{ route('socios.edit', $socio) }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Editar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 p-4 text-sm text-green-700 sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 bg-white p-6 shadow-sm sm:rounded-lg md:grid-cols-3">
                <p><span class="text-gray-500">Codigo:</span> {{ $socio->codigo_pago ?? '-' }}</p>
                <p><span class="text-gray-500">Casa:</span> {{ $socio->numero_casa ?? '-' }}</p>
                <p><span class="text-gray-500">Estado:</span> {{ ucfirst($socio->estado) }}</p>
                <p><span class="text-gray-500">Telefono:</span> {{ $socio->telefono ?? '-' }}</p>
                <p><span class="text-gray-500">Email:</span> {{ $socio->email ?? '-' }}</p>
                <p><span class="text-gray-500">Sector:</span> {{ $socio->sector ?? '-' }}</p>
                <p><span class="text-gray-500">Incorporacion:</span> {{ $socio->fecha_incorporacion?->format('d/m/Y') ?? '-' }}</p>
                <p><span class="text-gray-500">Nacimiento:</span> {{ $socio->fecha_nacimiento?->format('d/m/Y') ?? '-' }}</p>
                <p><span class="text-gray-500">RUT:</span> {{ $socio->rut ?? '-' }}</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 p-6"><h3 class="font-semibold text-gray-900">Historial de cuotas</h3></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($socio->cuotas as $cuota)
                                <tr>
                                    <td class="px-6 py-4">{{ $cuota->mes }}/{{ $cuota->anio }}</td>
                                    <td class="px-6 py-4 capitalize">{{ $cuota->estado }}</td>
                                    <td class="px-6 py-4 text-right">${{ number_format($cuota->monto, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td class="px-6 py-8 text-center text-gray-500">Sin cuotas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
