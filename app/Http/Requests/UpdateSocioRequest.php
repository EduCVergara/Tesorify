<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSocioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $socioId = $this->route('socio')?->id;

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'rut' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'numero_casa' => ['nullable', 'string', 'max:50'],
            'sector' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'fecha_incorporacion' => ['nullable', 'date'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'codigo_pago' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('socios', 'codigo_pago')->ignore($socioId),
            ],
            'estado' => ['required', 'in:activo,inactivo'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
