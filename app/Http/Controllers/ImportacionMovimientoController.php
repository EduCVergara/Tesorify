<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcesarImportacionMovimientosRequest;
use App\Http\Requests\SubirImportacionMovimientosRequest;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportacionMovimientoController extends Controller
{
    public function index()
    {
        return view('importaciones.movimientos.index', [
            'movimientosImportados' => Movimiento::whereIn('fuente', ['importacion_excel', 'mercado_pago'])
                ->latest('created_at')
                ->limit(12)
                ->get(),
        ]);
    }

    public function preview(SubirImportacionMovimientosRequest $request)
    {
        $archivo = $request->file('archivo');
        $path = $archivo->store('importaciones');
        $sheet = $this->sheet($path);
        $rows = $this->rows($sheet, 15);

        return view('importaciones.movimientos.preview', [
            'archivoPath' => $path,
            'archivoNombre' => $archivo->getClientOriginalName(),
            'columnas' => $this->columnOptions($sheet),
            'filas' => $rows,
            'filaInicio' => $this->suggestStartRow($rows),
        ]);
    }

    public function store(ProcesarImportacionMovimientosRequest $request)
    {
        $data = $request->validated();
        abort_unless(Storage::exists($data['archivo_path']), 404);

        $sheet = $this->sheet($data['archivo_path']);
        $highestRow = $sheet->getHighestDataRow();
        $creados = 0;
        $duplicados = 0;
        $omitidos = 0;

        for ($row = (int) $data['fila_inicio']; $row <= $highestRow; $row++) {
            $fecha = $this->normalDate($sheet->getCell("{$data['fecha_col']}{$row}")->getValue());
            $descripcion = $this->cellText($sheet, $data['descripcion_col'], $row);
            $nombreOrigen = $this->optionalCellText($sheet, $data['nombre_origen_col'] ?? null, $row);
            $categoria = $this->optionalCellText($sheet, $data['categoria_col'] ?? null, $row);
            $saldo = $this->optionalMoney($this->optionalCellValue($sheet, $data['saldo_col'] ?? null, $row));
            $montoOriginal = $this->normalMoney($sheet->getCell("{$data['monto_col']}{$row}")->getCalculatedValue());
            $tipo = $this->tipoMovimiento($this->optionalCellText($sheet, $data['tipo_col'] ?? null, $row), $montoOriginal, "{$descripcion} {$nombreOrigen}");
            $monto = abs($montoOriginal);

            if (! $fecha || $descripcion === '' || $monto <= 0) {
                $omitidos++;

                continue;
            }

            $hash = $this->importHash($fecha, $monto, $descripcion, $nombreOrigen);

            if ($this->alreadyImported($hash, $fecha, $tipo, $monto, $descripcion, $nombreOrigen)) {
                $duplicados++;

                continue;
            }

            Movimiento::create([
                'fecha' => $fecha,
                'tipo' => $tipo,
                'tipo_deposito' => $tipo === 'ingreso' ? $this->tipoDeposito("{$descripcion} {$nombreOrigen}") : null,
                'categoria' => $categoria ?: null,
                'descripcion' => $descripcion,
                'nombre_origen' => $nombreOrigen ?: null,
                'monto' => $monto,
                'saldo' => $saldo,
                'fuente' => 'importacion_excel',
                'estado_conciliacion' => 'pendiente',
                'datos_originales' => [
                    'archivo' => $data['archivo_nombre'],
                    'fila' => $row,
                    'valores' => $this->rowSnapshot($sheet, $row),
                ],
                'hash_importacion' => $hash,
            ]);

            $creados++;
        }

        return redirect()
            ->route('importaciones.movimientos.index')
            ->with('status', "Importacion finalizada. Nuevos: {$creados}. Duplicados: {$duplicados}. Omitidos: {$omitidos}.");
    }

    private function sheet(string $path): Worksheet
    {
        return IOFactory::load(Storage::path($path))->getActiveSheet();
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function rows(Worksheet $sheet, int $limit): array
    {
        $highestColumn = $sheet->getHighestDataColumn();
        $highestRow = min($sheet->getHighestDataRow(), $limit);
        $rows = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $rows[$row] = $this->rowSnapshot($sheet, $row, $highestColumn);
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function columnOptions(Worksheet $sheet): array
    {
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $options = [];

        for ($columnIndex = 1; $columnIndex <= $highestColumnIndex; $columnIndex++) {
            $letter = Coordinate::stringFromColumnIndex($columnIndex);
            $samples = [];

            for ($row = 1; $row <= min($sheet->getHighestDataRow(), 6); $row++) {
                $value = trim((string) $sheet->getCell("{$letter}{$row}")->getCalculatedValue());

                if ($value !== '') {
                    $samples[] = $value;
                }
            }

            $options[$letter] = $letter.(count($samples) ? ' - '.implode(' / ', array_slice($samples, 0, 2)) : '');
        }

        return $options;
    }

    /**
     * @param  array<int, array<string, string|null>>  $rows
     */
    private function suggestStartRow(array $rows): int
    {
        foreach ($rows as $rowNumber => $values) {
            $line = mb_strtolower(implode(' ', array_filter($values)));

            if (str_contains($line, 'fecha')) {
                return $rowNumber + 1;
            }
        }

        return 2;
    }

    /**
     * @return array<string, string|null>
     */
    private function rowSnapshot(Worksheet $sheet, int $row, ?string $highestColumn = null): array
    {
        $highestColumn ??= $sheet->getHighestDataColumn();
        $values = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, true)[$row];

        return array_map(
            fn ($value) => is_null($value) ? null : trim((string) $value),
            $values,
        );
    }

    private function cellText(Worksheet $sheet, string $column, int $row): string
    {
        return trim((string) $sheet->getCell("{$column}{$row}")->getCalculatedValue());
    }

    private function optionalCellText(Worksheet $sheet, ?string $column, int $row): string
    {
        return $column ? $this->cellText($sheet, $column, $row) : '';
    }

    private function optionalCellValue(Worksheet $sheet, ?string $column, int $row): mixed
    {
        return $column ? $sheet->getCell("{$column}{$row}")->getCalculatedValue() : null;
    }

    private function normalDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        $value = trim((string) $value);

        foreach (['d/m/Y', 'j/n/Y', 'm/d/Y', 'n/j/Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                return $date->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalMoney(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim(str_replace(['$', ' '], '', (string) $value));

        if (preg_match('/^-?\d{1,3}(,\d{3})+$/', $value)) {
            return (float) str_replace(',', '', $value);
        }

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function optionalMoney(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->normalMoney($value);
    }

    private function tipoMovimiento(string $tipo, float $monto, string $contexto): string
    {
        $texto = $this->normalText("{$tipo} {$contexto}");

        if (str_contains($texto, 'cargo') || str_contains($texto, 'egreso') || str_contains($texto, 'retiro') || str_contains($texto, 'debito')) {
            return 'egreso';
        }

        if (str_contains($texto, 'abono') || str_contains($texto, 'ingreso') || str_contains($texto, 'deposito') || str_contains($texto, 'transferencia')) {
            return 'ingreso';
        }

        return $monto < 0 ? 'egreso' : 'ingreso';
    }

    private function tipoDeposito(string $contexto): string
    {
        $texto = $this->normalText($contexto);

        if (str_contains($texto, 'efectivo')) {
            return 'efectivo';
        }

        if (str_contains($texto, 'cheque')) {
            return 'cheque';
        }

        return 'transferencia';
    }

    private function importHash(string $fecha, float $monto, string $descripcion, string $nombreOrigen): string
    {
        return sha1(implode('|', [
            $fecha,
            number_format($monto, 2, '.', ''),
            $this->normalText($descripcion),
            $this->normalText($nombreOrigen),
        ]));
    }

    private function alreadyImported(string $hash, string $fecha, string $tipo, float $monto, string $descripcion, string $nombreOrigen): bool
    {
        return Movimiento::where('hash_importacion', $hash)->exists()
            || Movimiento::whereDate('fecha', $fecha)
                ->where('tipo', $tipo)
                ->where('monto', $monto)
                ->where('descripcion', $descripcion)
                ->where('nombre_origen', $nombreOrigen ?: null)
                ->exists();
    }

    private function normalText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;

        return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
    }
}
