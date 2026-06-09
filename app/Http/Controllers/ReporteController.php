<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Http\Requests\ReporteRequest;
use App\Models\Cuota;
use App\Models\Movimiento;
use App\Models\Socio;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    private const REPORTES = [
        'libro-banco',
        'nomina-cuotas',
        'deuda-cuotas',
        'gastos-mes',
        'deudores',
        'ingresos-gastos-rango',
        'sector',
    ];

    public function index()
    {
        return view('reportes.index', [
            'anioActual' => now()->year,
            'mesActual' => now()->month,
            'inicioMes' => now()->startOfMonth()->toDateString(),
            'finMes' => now()->endOfMonth()->toDateString(),
            'sectores' => Socio::whereNotNull('sector')->distinct()->orderBy('sector')->pluck('sector'),
        ]);
    }

    public function descargar(ReporteRequest $request, string $reporte)
    {
        abort_unless(in_array($reporte, self::REPORTES, true), 404);

        $data = $request->validated();
        [$headings, $rows, $titulo, $archivo] = match ($reporte) {
            'libro-banco' => $this->libroBanco($data),
            'nomina-cuotas' => $this->nominaCuotas($data),
            'deuda-cuotas' => $this->deudaCuotas($data),
            'gastos-mes' => $this->gastosMes($data),
            'deudores' => $this->deudores(),
            'ingresos-gastos-rango' => $this->ingresosGastosRango($data),
            'sector' => $this->sector($data),
        };

        return Excel::download(new ReporteExport($headings, $rows, $titulo), $archivo);
    }

    private function libroBanco(array $data): array
    {
        [$desde, $hasta] = $this->rangoFechas($data);

        $rows = Movimiento::with('socio')
            ->whereBetween('fecha', [$desde, $hasta])
            ->oldest('fecha')
            ->oldest('id')
            ->get()
            ->map(fn (Movimiento $movimiento) => [
                $movimiento->fecha->format('d/m/Y'),
                $movimiento->tipo,
                $movimiento->tipo_deposito,
                $movimiento->categoria,
                $movimiento->descripcion,
                $movimiento->nombre_origen,
                $movimiento->socio?->nombre,
                (float) $movimiento->monto,
                $movimiento->saldo ? (float) $movimiento->saldo : null,
                $movimiento->fuente,
                $movimiento->estado_conciliacion,
            ])->all();

        return [
            ['Fecha', 'Tipo', 'Tipo deposito', 'Categoria', 'Descripcion', 'Origen', 'Socio', 'Monto', 'Saldo', 'Fuente', 'Conciliacion'],
            $rows,
            'Libro banco',
            "libro-banco-{$desde->format('Ymd')}-{$hasta->format('Ymd')}.xlsx",
        ];
    }

    private function nominaCuotas(array $data): array
    {
        [$anio, $mes] = $this->periodo($data);

        $rows = Cuota::with('socio')
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->orderBy(Socio::select('nombre')->whereColumn('socios.id', 'cuotas.socio_id'))
            ->get()
            ->map(fn (Cuota $cuota) => [
                "{$cuota->mes}/{$cuota->anio}",
                $cuota->socio->nombre,
                $cuota->socio->codigo_pago,
                $cuota->socio->numero_casa,
                (float) $cuota->monto,
                $cuota->estado,
                $cuota->fecha_pago?->format('d/m/Y'),
            ])->all();

        return [
            ['Periodo', 'Socio', 'Codigo pago', 'Casa', 'Monto', 'Estado', 'Fecha pago'],
            $rows,
            'Nomina cuotas',
            "nomina-cuotas-{$anio}-{$mes}.xlsx",
        ];
    }

    private function deudaCuotas(array $data): array
    {
        [$anio, $mes] = $this->periodo($data);

        $rows = Cuota::with('socio')
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->orderBy(Socio::select('nombre')->whereColumn('socios.id', 'cuotas.socio_id'))
            ->get()
            ->map(fn (Cuota $cuota) => [
                "{$cuota->mes}/{$cuota->anio}",
                $cuota->socio->nombre,
                $cuota->socio->codigo_pago,
                $cuota->socio->numero_casa,
                (float) $cuota->monto,
                $cuota->estado,
            ])->all();

        return [
            ['Periodo', 'Socio', 'Codigo pago', 'Casa', 'Monto deuda', 'Estado'],
            $rows,
            'Deuda cuotas',
            "deuda-cuotas-{$anio}-{$mes}.xlsx",
        ];
    }

    private function gastosMes(array $data): array
    {
        [$anio, $mes] = $this->periodo($data);

        $rows = Movimiento::where('tipo', 'egreso')
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->oldest('fecha')
            ->get()
            ->map(fn (Movimiento $movimiento) => [
                $movimiento->fecha->format('d/m/Y'),
                $movimiento->categoria,
                $movimiento->descripcion,
                (float) $movimiento->monto,
                $movimiento->fuente,
            ])->all();

        return [
            ['Fecha', 'Categoria', 'Descripcion', 'Monto', 'Fuente'],
            $rows,
            'Gastos del mes',
            "gastos-{$anio}-{$mes}.xlsx",
        ];
    }

    private function deudores(): array
    {
        $rows = Socio::with(['cuotas' => fn ($query) => $query->whereIn('estado', ['pendiente', 'parcial'])])
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get()
            ->map(function (Socio $socio) {
                $deuda = $socio->cuotas->sum('monto');

                return [
                    'socio' => $socio->nombre,
                    'codigo' => $socio->codigo_pago,
                    'casa' => $socio->numero_casa,
                    'cuotas' => $socio->cuotas->count(),
                    'deuda' => (float) $deuda,
                ];
            })
            ->filter(fn (array $row) => $row['cuotas'] > 0)
            ->map(fn (array $row) => [$row['socio'], $row['codigo'], $row['casa'], $row['cuotas'], $row['deuda']])
            ->values()
            ->all();

        return [
            ['Socio', 'Codigo pago', 'Casa', 'Cuotas adeudadas', 'Deuda total'],
            $rows,
            'Deudores',
            'deudores.xlsx',
        ];
    }

    private function ingresosGastosRango(array $data): array
    {
        [$desde, $hasta] = $this->rangoFechas($data);

        $movimientos = Movimiento::whereIn('tipo', ['ingreso', 'egreso'])
            ->whereBetween('fecha', [$desde, $hasta])
            ->oldest('fecha')
            ->get();

        $rows = $movimientos
            ->map(fn (Movimiento $movimiento) => [
                $movimiento->fecha->format('d/m/Y'),
                $movimiento->tipo,
                $movimiento->tipo_deposito,
                $movimiento->categoria,
                $movimiento->descripcion,
                (float) $movimiento->monto,
            ])
            ->push(['', '', '', '', 'Total ingresos', (float) $movimientos->where('tipo', 'ingreso')->sum('monto')])
            ->push(['', '', '', '', 'Total egresos', (float) $movimientos->where('tipo', 'egreso')->sum('monto')])
            ->values()
            ->all();

        return [
            ['Fecha', 'Tipo', 'Tipo deposito', 'Categoria', 'Descripcion', 'Monto'],
            $rows,
            'Ingresos y gastos',
            "ingresos-gastos-{$desde->format('Ymd')}-{$hasta->format('Ymd')}.xlsx",
        ];
    }

    private function sector(array $data): array
    {
        $sector = $data['sector'] ?? null;

        $rows = Socio::with('cuotas')
            ->when($sector, fn ($query) => $query->where('sector', $sector))
            ->orderBy('sector')
            ->orderBy('nombre')
            ->get()
            ->map(function (Socio $socio) {
                $cuotasPagadas = $socio->cuotas->where('estado', 'pagada');
                $cuotasPendientes = $socio->cuotas->whereIn('estado', ['pendiente', 'parcial']);

                return [
                    $socio->sector ?? 'Sin sector',
                    $socio->nombre,
                    $socio->codigo_pago,
                    $socio->numero_casa,
                    $cuotasPagadas->count(),
                    (float) $cuotasPagadas->sum('monto'),
                    $cuotasPendientes->count(),
                    (float) $cuotasPendientes->sum('monto'),
                    $socio->telefono,
                ];
            })
            ->all();

        $filenameSector = $sector ? str($sector)->slug()->toString() : 'todos';

        return [
            ['Sector', 'Socio', 'Codigo pago', 'Casa', 'Cuotas pagadas', 'Monto pagado', 'Cuotas pendientes', 'Deuda', 'Telefono'],
            $rows,
            'Reporte por sector',
            "reporte-sector-{$filenameSector}.xlsx",
        ];
    }

    private function periodo(array $data): array
    {
        return [
            (int) ($data['anio'] ?? now()->year),
            (int) ($data['mes'] ?? now()->month),
        ];
    }

    private function rangoFechas(array $data): array
    {
        return [
            Carbon::parse($data['desde'] ?? now()->startOfMonth())->startOfDay(),
            Carbon::parse($data['hasta'] ?? now()->endOfMonth())->endOfDay(),
        ];
    }
}
