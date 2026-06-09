<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcesarImportacionMovimientosRequest extends FormRequest
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
            'archivo_path' => ['required', 'string'],
            'archivo_nombre' => ['required', 'string', 'max:255'],
            'fila_inicio' => ['required', 'integer', 'min:1', 'max:5000'],
            'fecha_col' => ['required', 'string', 'max:5'],
            'descripcion_col' => ['required', 'string', 'max:5'],
            'nombre_origen_col' => ['nullable', 'string', 'max:5'],
            'monto_col' => ['required', 'string', 'max:5'],
            'tipo_col' => ['nullable', 'string', 'max:5'],
            'categoria_col' => ['nullable', 'string', 'max:5'],
            'saldo_col' => ['nullable', 'string', 'max:5'],
        ];
    }
}
