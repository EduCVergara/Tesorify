<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Http\Requests\GenerarCuotasRequest;
use App\Models\Cuota;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CuotaController extends Controller
{
    public function index(Request $request)
    {
        $cuotas = $this->cuotasFiltradas($request)
            ->latest('anio')
            ->latest('mes')
            ->paginate(15)
            ->withQueryString();

        return view('cuotas.index', [
            'cuotas' => $cuotas,
            'socios' => Socio::orderBy('nombre')->get(),
        ]);
    }

    public function exportar(Request $request)
    {
        $rows = $this->cuotasFiltradas($request)
            ->latest('anio')
            ->latest('mes')
            ->get()
            ->map(fn (Cuota $cuota) => [
                "{$cuota->mes}/{$cuota->anio}",
                $cuota->socio->nombre,
                $cuota->socio->codigo_pago,
                $cuota->socio->numero_casa,
                $cuota->socio->sector,
                (float) $cuota->monto,
                $cuota->estado,
                $cuota->fecha_pago?->format('d/m/Y'),
            ])
            ->all();

        return Excel::download(
            new ReporteExport(
                ['Periodo', 'Socio', 'Codigo pago', 'Casa', 'Sector', 'Monto', 'Estado', 'Fecha pago'],
                $rows,
                'Cuotas filtradas',
            ),
            'cuotas-filtradas.xlsx',
        );
    }

    public function generar(GenerarCuotasRequest $request)
    {
        $data = $request->validated();
        $creadas = 0;

        Socio::where('estado', 'activo')
            ->orderBy('id')
            ->each(function (Socio $socio) use ($data, &$creadas) {
                $cuota = Cuota::firstOrCreate(
                    [
                        'socio_id' => $socio->id,
                        'anio' => $data['anio'],
                        'mes' => $data['mes'],
                    ],
                    [
                        'monto' => $data['monto'],
                        'estado' => 'pendiente',
                        'observaciones' => $data['observaciones'] ?? null,
                    ],
                );

                if ($cuota->wasRecentlyCreated) {
                    $creadas++;
                }
            });

        return redirect()
            ->route('cuotas.index', ['anio' => $data['anio'], 'mes' => $data['mes']])
            ->with('status', "Cuotas generadas: {$creadas}.");
    }

    public function pagar(Cuota $cuota)
    {
        $cuota->update([
            'estado' => 'pagada',
            'fecha_pago' => today(),
            'monto_pagado' => $cuota->monto,
        ]);

        return back()->with('status', 'Cuota marcada como pagada.');
    }

    private function cuotasFiltradas(Request $request): Builder
    {
        return Cuota::with('socio')
            ->when($request->filled('anio'), fn (Builder $query) => $query->where('anio', $request->integer('anio')))
            ->when($request->filled('mes'), fn (Builder $query) => $query->where('mes', $request->integer('mes')))
            ->when($request->filled('estado'), fn (Builder $query) => $query->where('estado', $request->input('estado')))
            ->when($request->filled('socio_id'), fn (Builder $query) => $query->where('socio_id', $request->integer('socio_id')));
    }
}
