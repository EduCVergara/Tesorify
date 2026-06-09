<?php

namespace Database\Seeders;

use App\Models\Movimiento;
use Database\Seeders\Concerns\ReadsSeedExcel;
use Illuminate\Database\Seeder;

class MovimientosDesdeExcelSeeder extends Seeder
{
    use ReadsSeedExcel;

    public function run(): void
    {
        $workbook = $this->workbook('Libro-Banco.xlsx');

        $this->importLibroBanco($workbook->getSheetByName('Hoja1'));
        $this->importGastos($workbook->getSheetByName('Hoja2'));
        $this->importGastos2026($workbook->getSheetByName('Hoja3'));
    }

    private function importLibroBanco($sheet): void
    {
        for ($row = 6; $row <= $sheet->getHighestDataRow(); $row++) {
            $fecha = $this->dateFromCell($sheet, "B{$row}");
            $nombre = $this->text($sheet, "C{$row}");
            $finalidad = $this->text($sheet, "D{$row}");
            $abono = $this->money($this->text($sheet, "E{$row}"));
            $cargo = $this->money($this->text($sheet, "F{$row}"));
            $saldo = $this->money($this->text($sheet, "G{$row}"));

            if (! $fecha || ($abono <= 0 && $cargo <= 0)) {
                continue;
            }

            $tipo = $cargo > 0 ? 'egreso' : 'ingreso';
            $monto = $cargo > 0 ? $cargo : $abono;

            Movimiento::updateOrCreate([
                'fecha' => $fecha,
                'tipo' => $tipo,
                'descripcion' => $finalidad !== '' ? $finalidad : $nombre,
                'nombre_origen' => $nombre !== '' ? $nombre : null,
                'monto' => $monto,
                'saldo' => $saldo ?: null,
            ], [
                'tipo_deposito' => $tipo === 'ingreso' ? $this->tipoDeposito($nombre) : null,
                'categoria' => $finalidad ?: null,
                'fuente' => 'importacion_excel',
                'estado_conciliacion' => 'pendiente',
                'datos_originales' => [
                    'archivo' => 'Libro-Banco.xlsx',
                    'hoja' => 'Hoja1',
                    'fila' => $row,
                    'valores' => $this->rowSnapshot($sheet, $row),
                ],
            ]);
        }
    }

    private function importGastos($sheet): void
    {
        for ($row = 3; $row <= $sheet->getHighestDataRow(); $row++) {
            $fecha = $this->dateFromCell($sheet, "A{$row}");
            $proveedor = $this->text($sheet, "B{$row}");
            $concepto = $this->text($sheet, "C{$row}");
            $valor = $this->money($this->text($sheet, "D{$row}"));

            if (! $fecha || $valor <= 0) {
                continue;
            }

            $this->storeGasto($fecha, $proveedor, $concepto, $valor, 'Hoja2', $row, $sheet);
        }
    }

    private function importGastos2026($sheet): void
    {
        for ($row = 12; $row <= $sheet->getHighestDataRow(); $row++) {
            $fecha = $this->dateFromCell($sheet, "A{$row}");
            $proveedor = $this->text($sheet, "B{$row}");
            $concepto = $this->text($sheet, "C{$row}");
            $valor = $this->money($this->text($sheet, "D{$row}"));

            if (! $fecha || $valor <= 0) {
                continue;
            }

            $this->storeGasto($fecha, $proveedor, $concepto, $valor, 'Hoja3', $row, $sheet);
        }
    }

    private function storeGasto(string $fecha, string $proveedor, string $concepto, int $valor, string $sheetName, int $row, $sheet): void
    {
        Movimiento::updateOrCreate([
            'fecha' => $fecha,
            'tipo' => 'egreso',
            'descripcion' => $concepto !== '' ? $concepto : 'Gasto importado',
            'nombre_origen' => $proveedor !== '' ? $proveedor : null,
            'monto' => $valor,
        ], [
            'categoria' => 'Gastos',
            'fuente' => 'importacion_excel',
            'estado_conciliacion' => 'pendiente',
            'datos_originales' => [
                'archivo' => 'Libro-Banco.xlsx',
                'hoja' => $sheetName,
                'fila' => $row,
                'valores' => $this->rowSnapshot($sheet, $row),
            ],
        ]);
    }

    private function tipoDeposito(string $nombre): string
    {
        $nombre = $this->normalize($nombre);

        if (str_contains($nombre, 'efectivo')) {
            return 'efectivo';
        }

        if (str_contains($nombre, 'cheque')) {
            return 'cheque';
        }

        return 'transferencia';
    }
}
