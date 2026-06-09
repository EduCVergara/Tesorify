<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmarConciliacionRequest;
use App\Models\Cuota;
use App\Models\Movimiento;
use App\Models\Socio;
use App\Services\ConciliacionSugeridor;
use Illuminate\Support\Facades\DB;

class ConciliacionController extends Controller
{
    public function index(ConciliacionSugeridor $sugeridor)
    {
        $movimientos = Movimiento::where('tipo', 'ingreso')
            ->where('estado_conciliacion', '!=', 'conciliado')
            ->latest('fecha')
            ->latest('id')
            ->paginate(12)
            ->through(fn (Movimiento $movimiento) => [
                'movimiento' => $movimiento,
                'sugerencia' => $sugeridor->sugerencias($movimiento)->first(),
            ]);

        return view('conciliaciones.index', compact('movimientos'));
    }

    public function show(Movimiento $movimiento, ConciliacionSugeridor $sugeridor)
    {
        abort_if($movimiento->tipo !== 'ingreso', 404);

        $socios = Socio::where('estado', 'activo')->orderBy('nombre')->get();
        $sugerencias = $sugeridor->sugerencias($movimiento);
        $socioSeleccionado = request('socio_id')
            ? Socio::find(request('socio_id'))
            : ($sugerencias->first()['socio'] ?? null);

        $cuotasPendientes = $socioSeleccionado
            ? Cuota::where('socio_id', $socioSeleccionado->id)
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->orderBy('anio')
                ->orderBy('mes')
                ->get()
            : collect();

        return view('conciliaciones.show', compact('movimiento', 'socios', 'sugerencias', 'socioSeleccionado', 'cuotasPendientes'));
    }

    public function confirmar(ConfirmarConciliacionRequest $request, Movimiento $movimiento)
    {
        abort_if($movimiento->tipo !== 'ingreso', 404);

        $data = $request->validated();

        DB::transaction(function () use ($data, $movimiento) {
            $movimiento->update([
                'socio_id' => $data['socio_id'],
                'estado_conciliacion' => 'conciliado',
            ]);

            $restante = (float) $movimiento->monto;
            $cuotas = Cuota::whereIn('id', $data['cuota_ids'] ?? [])
                ->where('socio_id', $data['socio_id'])
                ->orderBy('anio')
                ->orderBy('mes')
                ->get();

            foreach ($cuotas as $cuota) {
                if ($restante <= 0) {
                    break;
                }

                $saldoCuota = max((float) $cuota->monto - (float) $cuota->monto_pagado, 0);
                $abono = min($restante, $saldoCuota);
                $nuevoPagado = (float) $cuota->monto_pagado + $abono;
                $pagada = $nuevoPagado >= (float) $cuota->monto;

                $cuota->update([
                    'monto_pagado' => $nuevoPagado,
                    'estado' => $pagada ? 'pagada' : 'parcial',
                    'fecha_pago' => $pagada ? today() : $cuota->fecha_pago,
                    'movimiento_id' => $movimiento->id,
                    'observaciones' => trim(($cuota->observaciones ? "{$cuota->observaciones}\n" : '').($data['observaciones'] ?? 'Conciliada manualmente.')),
                ]);

                $restante -= $abono;
            }
        });

        return redirect()
            ->route('conciliaciones.index')
            ->with('status', 'Movimiento conciliado correctamente.');
    }
}
