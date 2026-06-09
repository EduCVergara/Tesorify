<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\Socio;
use Illuminate\Support\Collection;

class ConciliacionSugeridor
{
    /**
     * @return Collection<int, array{socio: Socio, puntaje: int, nivel: string, razones: array<int, string>}>
     */
    public function sugerencias(Movimiento $movimiento): Collection
    {
        $texto = $this->normalizar("{$movimiento->descripcion} {$movimiento->nombre_origen}");

        return Socio::with(['cuotas' => fn ($query) => $query->whereIn('estado', ['pendiente', 'parcial'])])
            ->where('estado', 'activo')
            ->get()
            ->map(function (Socio $socio) use ($movimiento, $texto) {
                [$puntaje, $razones] = $this->puntuar($movimiento, $socio, $texto);

                return [
                    'socio' => $socio,
                    'puntaje' => min($puntaje, 100),
                    'nivel' => $puntaje >= 70 ? 'alta' : ($puntaje >= 45 ? 'media' : 'baja'),
                    'razones' => $razones,
                ];
            })
            ->filter(fn (array $sugerencia) => $sugerencia['puntaje'] >= 25)
            ->sortByDesc('puntaje')
            ->take(8)
            ->values();
    }

    /**
     * @return array{0: int, 1: array<int, string>}
     */
    private function puntuar(Movimiento $movimiento, Socio $socio, string $texto): array
    {
        $puntaje = 0;
        $razones = [];

        if ($socio->codigo_pago && str_contains($texto, $this->normalizar($socio->codigo_pago))) {
            $puntaje += 60;
            $razones[] = 'Codigo de pago encontrado';
        }

        similar_text($this->normalizar((string) $movimiento->nombre_origen), $this->normalizar($socio->nombre), $similitudOrigen);
        similar_text($this->normalizar($movimiento->descripcion), $this->normalizar($socio->nombre), $similitudDescripcion);
        $similitud = max($similitudOrigen, $similitudDescripcion);

        if ($similitud >= 80) {
            $puntaje += 35;
            $razones[] = 'Nombre muy parecido';
        } elseif ($similitud >= 55) {
            $puntaje += 20;
            $razones[] = 'Nombre parecido';
        }

        $pendientes = $socio->cuotas;
        $saldos = $pendientes->map(fn ($cuota) => max((float) $cuota->monto - (float) $cuota->monto_pagado, 0));

        if ($saldos->contains((float) $movimiento->monto)) {
            $puntaje += 20;
            $razones[] = 'Monto coincide con una cuota pendiente';
        }

        if ($saldos->sum() > 0 && (float) $movimiento->monto <= (float) $saldos->sum()) {
            $puntaje += 15;
            $razones[] = 'Monto puede cubrir cuotas acumuladas';
        }

        $historial = Movimiento::where('estado_conciliacion', 'conciliado')
            ->where('socio_id', $socio->id)
            ->whereNotNull('nombre_origen')
            ->where('nombre_origen', $movimiento->nombre_origen)
            ->exists();

        if ($historial) {
            $puntaje += 25;
            $razones[] = 'Origen conciliado antes con este socio';
        }

        return [$puntaje, $razones];
    }

    private function normalizar(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;

        return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
    }
}
