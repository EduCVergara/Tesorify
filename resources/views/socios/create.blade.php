<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo socio</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto bg-white p-6 shadow-sm sm:rounded-lg sm:px-8">
            <form method="POST" action="{{ route('socios.store') }}">
                @include('socios._form')
            </form>
        </div>
    </div>
</x-app-layout>
