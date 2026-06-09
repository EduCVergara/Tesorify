<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nombre',
    'rut',
    'direccion',
    'numero_casa',
    'sector',
    'telefono',
    'email',
    'codigo_pago',
    'fecha_incorporacion',
    'fecha_nacimiento',
    'estado',
    'observaciones',
    'datos_originales',
])]
class Socio extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'fecha_incorporacion' => 'date',
            'fecha_nacimiento' => 'date',
            'datos_originales' => 'array',
        ];
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => trim($value),
        );
    }
}
