<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMovimientoRequest extends FormRequest
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
        return [
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'in:ingreso,egreso,ajuste'],
            'tipo_deposito' => ['nullable', 'in:transferencia,efectivo,cheque'],
            'categoria' => ['nullable', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'max:255'],
            'nombre_origen' => ['nullable', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'saldo' => ['nullable', 'numeric', 'min:-999999999', 'max:999999999'],
            'fuente' => ['nullable', 'in:manual,mercado_pago,importacion_excel,ajuste'],
            'estado_conciliacion' => ['nullable', 'in:conciliado,pendiente,dudoso'],
            'socio_id' => ['nullable', 'exists:socios,id'],
            'cuota_id' => ['nullable', 'exists:cuotas,id'],
        ];
    }
}
