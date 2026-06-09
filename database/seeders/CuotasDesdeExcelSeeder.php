<?php

namespace Database\Seeders;

use App\Models\Cuota;
use App\Models\Socio;
use Database\Seeders\Concerns\ReadsSeedExcel;
use Illuminate\Database\Seeder;

class CuotasDesdeExcelSeeder extends Seeder
{
    use ReadsSeedExcel;

    public function run(): void
    {
        $workbook = $this->workbook('Cuotas.xlsx');

        $this->importSheet($workbook->getSheetByName('Cuotas2025'), [
            6 => [2025, 10],
            7 => [2025, 11],
            8 => [2025, 12],
        ], 'Cuotas.xlsx:Cuotas2025');

        $this->importSheet($workbook->getSheetByName('CUOTAS2026'), [
            6 => [2026, 1],
            7 => [2026, 2],
            8 => [2026, 3],
            9 => [2026, 4],
            10 => [2026, 5],
            11 => [2026, 6],
            12 => [2026, 7],
            13 => [2026, 8],
            14 => [2026, 9],
            15 => [2026, 10],
            16 => [2026, 11],
            17 => [2026, 12],
        ], 'Cuotas.xlsx:CUOTAS2026');
    }

    private function importSheet($sheet, array $monthColumns, string $origin): void
    {
        for ($row = 4; $row <= $sheet->getHighestDataRow(); $row++) {
            $nombre = $this->text($sheet, "B{$row}");

            if ($nombre === '') {
                continue;
            }

            $numeroCasa = $this->text($sheet, "E{$row}");
            $socio = $this->resolveSocio($nombre, $numeroCasa, $sheet, $row);

            foreach ($monthColumns as $column => [$anio, $mes]) {
                $amount = $this->money($sheet->getCellByColumnAndRow($column, $row)->getCalculatedValue());
                $isPastOrCurrent = $anio < now()->year || ($anio === now()->year && $mes <= now()->month);

                if ($amount <= 0 && ! $isPastOrCurrent) {
                    continue;
                }

                Cuota::updateOrCreate([
                    'socio_id' => $socio->id,
                    'anio' => $anio,
                    'mes' => $mes,
                ], [
                    'monto' => $amount > 0 ? $amount : 5000,
                    'monto_pagado' => $amount > 0 ? $amount : 0,
                    'estado' => $amount > 0 ? 'pagada' : 'pendiente',
                    'fecha_pago' => null,
                    'observaciones' => $amount > 0 ? 'Importada como pagada desde planilla.' : 'Importada como pendiente desde planilla.',
                    'origen_importacion' => $origin,
                    'datos_originales' => [
                        'archivo' => 'Cuotas.xlsx',
                        'hoja' => $sheet->getTitle(),
                        'fila' => $row,
                        'columna' => $column,
                        'valores' => $this->rowSnapshot($sheet, $row),
                    ],
                ]);
            }
        }
    }

    private function resolveSocio(string $nombre, string $numeroCasa, $sheet, int $row): Socio
    {
        $normalized = $this->normalize($nombre);

        $socio = Socio::all()->first(fn (Socio $socio) => $this->normalize($socio->nombre) === $normalized);

        if ($socio) {
            return $socio;
        }

        if ($numeroCasa !== '') {
            $sociosPorCasa = Socio::where('numero_casa', $numeroCasa)->get();

            if ($sociosPorCasa->count() === 1) {
                return $sociosPorCasa->first();
            }
        }

        return Socio::create([
            'nombre' => $nombre,
            'direccion' => $this->text($sheet, "C{$row}") ?: null,
            'numero_casa' => $numeroCasa ?: null,
            'codigo_pago' => $numeroCasa !== '' ? "CASA-{$numeroCasa}-IMPORT-{$row}" : "SOCIO-IMPORT-{$row}",
            'fecha_incorporacion' => $this->dateFromCell($sheet, "D{$row}"),
            'estado' => 'activo',
            'datos_originales' => [
                'archivo' => 'Cuotas.xlsx',
                'hoja' => $sheet->getTitle(),
                'fila' => $row,
                'valores' => $this->rowSnapshot($sheet, $row),
            ],
        ]);
    }
}
