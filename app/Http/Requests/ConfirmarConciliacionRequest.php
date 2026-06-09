<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmarConciliacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'socio_id' => ['required', 'exists:socios,id'],
            'cuota_ids' => ['nullable', 'array'],
            'cuota_ids.*' => ['integer', 'exists:cuotas,id'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
