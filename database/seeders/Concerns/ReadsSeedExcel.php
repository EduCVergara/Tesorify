<?php

namespace Database\Seeders\Concerns;

use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait ReadsSeedExcel
{
    protected function workbook(string $filename): Spreadsheet
    {
        $basePath = env('TESORIFY_SEED_DATA_PATH', 'C:\Users\Usuario\Downloads');
        $path = $basePath.DIRECTORY_SEPARATOR.$filename;

        if (! file_exists($path)) {
            throw new \RuntimeException("No se encontro el archivo de seed: {$path}");
        }

        return IOFactory::load($path);
    }

    protected function text(Worksheet $sheet, string $cell): string
    {
        $value = $sheet->getCell($cell)->getCalculatedValue();

        return trim((string) ($value ?? ''));
    }

    protected function money(mixed $value): int
    {
        return (int) str_replace([',', '.', '$', ' '], '', (string) ($value ?? ''));
    }

    protected function dateFromCell(Worksheet $sheet, string $cell): ?string
    {
        $value = $sheet->getCell($cell)->getValue();

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        $value = trim((string) $value);

        foreach (['d/m/Y', 'j/n/Y', 'm/d/Y', 'n/j/Y', 'Y-m-d'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);

            if ($date instanceof DateTimeImmutable) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    protected function rowSnapshot(Worksheet $sheet, int $row): array
    {
        $highestColumn = $sheet->getHighestDataColumn();
        $values = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, true)[$row];

        return array_map(
            fn ($value) => is_null($value) ? null : trim((string) $value),
            $values,
        );
    }

    protected function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;

        return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
    }
}
