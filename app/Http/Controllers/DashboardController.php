<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Movimiento;
use App\Models\Socio;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $ingresosMes = Movimiento::where('tipo', 'ingreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $egresosMes = Movimiento::where('tipo', 'egreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $saldoRegistrado = Movimiento::whereNotNull('saldo')
            ->latest('fecha')
            ->latest('id')
            ->value('saldo');

        $saldoActual = $saldoRegistrado ?? (
            Movimiento::where('tipo', 'ingreso')->sum('monto')
            - Movimiento::where('tipo', 'egreso')->sum('monto')
            + Movimiento::where('tipo', 'ajuste')->sum('monto')
        );

        $flujoMensual = collect(range(5, 0))
            ->map(function (int $mesesAtras) {
                $fecha = now()->subMonths($mesesAtras);

                return [
                    'periodo' => $fecha->format('m/Y'),
                    'ingresos' => (float) Movimiento::where('tipo', 'ingreso')
                        ->whereYear('fecha', $fecha->year)
                        ->whereMonth('fecha', $fecha->month)
                        ->sum('monto'),
                    'egresos' => (float) Movimiento::where('tipo', 'egreso')
                        ->whereYear('fecha', $fecha->year)
                        ->whereMonth('fecha', $fecha->month)
                        ->sum('monto'),
                ];
            });

        $deudasPorSocio = Socio::with(['cuotas' => fn ($query) => $query->whereIn('estado', ['pendiente', 'parcial'])])
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Socio $socio) => [
                'socio' => $socio->nombre,
                'deuda' => (float) $socio->cuotas->sum('monto'),
                'cuotas' => $socio->cuotas->count(),
            ])
            ->filter(fn (array $socio) => $socio['deuda'] > 0)
            ->sortByDesc('deuda')
            ->take(5)
            ->values();

        return view('dashboard', [
            'sociosActivos' => Socio::where('estado', 'activo')->count(),
            'ingresosMes' => $ingresosMes,
            'egresosMes' => $egresosMes,
            'saldoActual' => $saldoActual,
            'cuotasPendientes' => Cuota::whereIn('estado', ['pendiente', 'parcial'])->count(),
            'sociosMorosos' => Socio::where('estado', 'activo')
                ->whereHas('cuotas', fn ($query) => $query
                    ->whereIn('estado', ['pendiente', 'parcial'])
                    ->where(function ($query) {
                        $query->where('anio', '<', now()->year)
                            ->orWhere(function ($query) {
                                $query->where('anio', now()->year)
                                    ->where('mes', '<', now()->month);
                            });
                    }))
                ->count(),
            'ultimosMovimientos' => Movimiento::with('socio')
                ->latest('fecha')
                ->latest('id')
                ->limit(8)
                ->get(),
            'flujoMensual' => $flujoMensual,
            'deudasPorSocio' => $deudasPorSocio,
        ]);
    }
}
