<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'socio_id',
    'anio',
    'mes',
    'monto',
    'monto_pagado',
    'estado',
    'fecha_pago',
    'movimiento_id',
    'observaciones',
    'origen_importacion',
    'datos_originales',
])]
class Cuota extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'mes' => 'integer',
            'monto' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'fecha_pago' => 'date',
            'datos_originales' => 'array',
        ];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class);
    }
}
