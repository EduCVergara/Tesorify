<?php

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function value($sheet, string $cell): string
{
    $value = $sheet->getCell($cell)->getCalculatedValue();

    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }

    return trim((string) ($value ?? ''));
}

function money($value): int
{
    return (int) str_replace([',', '.', '$', ' '], '', (string) $value);
}

function dateValue($sheet, string $cell): ?string
{
    $cellValue = $sheet->getCell($cell)->getValue();

    if ($cellValue === null || $cellValue === '') {
        return null;
    }

    if (is_numeric($cellValue)) {
        return Date::excelToDateTimeObject((float) $cellValue)->format('Y-m-d');
    }

    $timestamp = strtotime((string) $cellValue);

    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

$nomina = IOFactory::load('C:\Users\Usuario\Downloads\Nomina Socios.xlsx');
$libro = IOFactory::load('C:\Users\Usuario\Downloads\Libro-Banco.xlsx');
$cuotas = IOFactory::load('C:\Users\Usuario\Downloads\Cuotas.xlsx');

$sociosSheet = $nomina->getSheetByName('Socios');
$socios = [];
for ($row = 4; $row <= $sociosSheet->getHighestDataRow(); $row++) {
    $nombre = value($sociosSheet, "B{$row}");
    if ($nombre === '') {
        continue;
    }

    $socios[] = [
        'nombre' => $nombre,
        'fecha_incorporacion' => dateValue($sociosSheet, "C{$row}"),
        'direccion' => value($sociosSheet, "D{$row}"),
        'numero_casa' => value($sociosSheet, "E{$row}"),
        'sector' => value($sociosSheet, "F{$row}"),
        'rut' => value($sociosSheet, "G{$row}"),
        'fecha_nacimiento' => dateValue($sociosSheet, "H{$row}"),
        'telefono' => value($sociosSheet, "I{$row}"),
    ];
}

$sectores = array_values(array_unique(array_filter(array_column($socios, 'sector'))));
sort($sectores);

$libroSheet = $libro->getSheetByName('Hoja1');
$movimientos = [];
for ($row = 6; $row <= $libroSheet->getHighestDataRow(); $row++) {
    $fecha = dateValue($libroSheet, "B{$row}");
    $nombre = value($libroSheet, "C{$row}");
    $finalidad = value($libroSheet, "D{$row}");
    $abono = money(value($libroSheet, "E{$row}"));
    $cargo = money(value($libroSheet, "F{$row}"));
    $saldo = money(value($libroSheet, "G{$row}"));

    if (! $fecha && $nombre === '' && $finalidad === '') {
        continue;
    }

    $movimientos[] = compact('fecha', 'nombre', 'finalidad', 'abono', 'cargo', 'saldo');
}

$gastosSheet = $libro->getSheetByName('Hoja2');
$gastos = [];
for ($row = 3; $row <= $gastosSheet->getHighestDataRow(); $row++) {
    $fecha = dateValue($gastosSheet, "A{$row}");
    $proveedor = value($gastosSheet, "B{$row}");
    $concepto = value($gastosSheet, "C{$row}");
    $valor = money(value($gastosSheet, "D{$row}"));

    if (! $fecha && $proveedor === '' && $concepto === '') {
        continue;
    }

    $gastos[] = compact('fecha', 'proveedor', 'concepto', 'valor');
}

$cuotasSummary = [];
foreach (['Cuotas2025', 'CUOTAS2026'] as $sheetName) {
    $sheet = $cuotas->getSheetByName($sheetName);
    $paidCells = 0;
    $totalPaid = 0;
    $sociosConFilas = 0;

    for ($row = 4; $row <= $sheet->getHighestDataRow(); $row++) {
        if (value($sheet, "B{$row}") === '') {
            continue;
        }

        $sociosConFilas++;

        for ($col = 6; $col <= 17; $col++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            $amount = money($cell);
            if ($amount > 0) {
                $paidCells++;
                $totalPaid += $amount;
            }
        }
    }

    $cuotasSummary[$sheetName] = compact('sociosConFilas', 'paidCells', 'totalPaid');
}

echo json_encode([
    'socios' => [
        'total' => count($socios),
        'con_rut' => count(array_filter(array_column($socios, 'rut'))),
        'con_fecha_nacimiento' => count(array_filter(array_column($socios, 'fecha_nacimiento'))),
        'con_fecha_incorporacion' => count(array_filter(array_column($socios, 'fecha_incorporacion'))),
        'sectores' => $sectores,
        'muestra' => array_slice($socios, 0, 5),
    ],
    'libro_banco' => [
        'movimientos' => count($movimientos),
        'abonos' => array_sum(array_column($movimientos, 'abono')),
        'cargos' => array_sum(array_column($movimientos, 'cargo')),
        'muestra' => array_slice($movimientos, 0, 5),
    ],
    'gastos' => [
        'total' => count($gastos),
        'monto' => array_sum(array_column($gastos, 'valor')),
        'muestra' => array_slice($gastos, 0, 5),
    ],
    'cuotas' => $cuotasSummary,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
