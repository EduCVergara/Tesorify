<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'fecha',
    'tipo',
    'tipo_deposito',
    'categoria',
    'descripcion',
    'nombre_origen',
    'monto',
    'saldo',
    'fuente',
    'estado_conciliacion',
    'socio_id',
    'cuota_id',
    'datos_originales',
    'hash_importacion',
])]
class Movimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
            'saldo' => 'decimal:2',
            'datos_originales' => 'array',
        ];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class);
    }
}
