@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="nombre" value="Nombre" />
        <x-text-input id="nombre" name="nombre" class="mt-1 block w-full" :value="old('nombre', $socio->nombre)" required />
        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
    </div>

    <div>
        <x-input-label for="codigo_pago" value="Codigo de pago" />
        <x-text-input id="codigo_pago" name="codigo_pago" class="mt-1 block w-full" :value="old('codigo_pago', $socio->codigo_pago)" placeholder="CASA-165" />
        <x-input-error class="mt-2" :messages="$errors->get('codigo_pago')" />
    </div>

    <div>
        <x-input-label for="rut" value="RUT" />
        <x-text-input id="rut" name="rut" class="mt-1 block w-full" :value="old('rut', $socio->rut)" />
    </div>

    <div>
        <x-input-label for="telefono" value="Telefono" />
        <x-text-input id="telefono" name="telefono" class="mt-1 block w-full" :value="old('telefono', $socio->telefono)" />
    </div>

    <div>
        <x-input-label for="direccion" value="Direccion" />
        <x-text-input id="direccion" name="direccion" class="mt-1 block w-full" :value="old('direccion', $socio->direccion)" />
    </div>

    <div>
        <x-input-label for="numero_casa" value="Numero de casa" />
        <x-text-input id="numero_casa" name="numero_casa" class="mt-1 block w-full" :value="old('numero_casa', $socio->numero_casa)" />
    </div>

    <div>
        <x-input-label for="sector" value="Sector" />
        <select id="sector" name="sector" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select data-tom-create="true">
            <option value="">Sin sector</option>
            @foreach (['Azul', 'Rojo', 'Amarillo', 'Verde'] as $sector)
                <option value="{{ $sector }}" @selected(old('sector', $socio->sector) === $sector)>{{ $sector }}</option>
            @endforeach
            @if ($socio->sector && ! in_array($socio->sector, ['Azul', 'Rojo', 'Amarillo', 'Verde'], true))
                <option value="{{ $socio->sector }}" selected>{{ $socio->sector }}</option>
            @endif
        </select>
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" type="email" name="email" class="mt-1 block w-full" :value="old('email', $socio->email)" />
    </div>

    <div>
        <x-input-label for="fecha_incorporacion" value="Fecha de incorporacion" />
        <x-text-input id="fecha_incorporacion" type="date" name="fecha_incorporacion" class="mt-1 block w-full" :value="old('fecha_incorporacion', $socio->fecha_incorporacion?->format('Y-m-d'))" />
        <x-input-error class="mt-2" :messages="$errors->get('fecha_incorporacion')" />
    </div>

    <div>
        <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
        <x-text-input id="fecha_nacimiento" type="date" name="fecha_nacimiento" class="mt-1 block w-full" :value="old('fecha_nacimiento', $socio->fecha_nacimiento?->format('Y-m-d'))" />
        <x-input-error class="mt-2" :messages="$errors->get('fecha_nacimiento')" />
    </div>

    <div>
        <x-input-label for="estado" value="Estado" />
        <select id="estado" name="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-tom-select>
            <option value="activo" @selected(old('estado', $socio->estado) === 'activo')>Activo</option>
            <option value="inactivo" @selected(old('estado', $socio->estado) === 'inactivo')>Inactivo</option>
        </select>
    </div>

    <div class="md:col-span-2">
        <x-input-label for="observaciones" value="Observaciones" />
        <textarea id="observaciones" name="observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('observaciones', $socio->observaciones) }}</textarea>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>Guardar</x-primary-button>
    <a href="{{ route('socios.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
