<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__.'/../vendor/autoload.php';

foreach (array_slice($argv, 1) as $path) {
    $spreadsheet = IOFactory::load($path);

    echo '==== '.basename($path)." ====\n";

    foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
        $highestRow = $sheet->getHighestDataRow();
        $highestCol = $sheet->getHighestDataColumn();

        echo "Sheet: {$sheet->getTitle()} rows={$highestRow} cols={$highestCol}\n";

        $max = min($highestRow, 15);

        for ($row = 1; $row <= $max; $row++) {
            $values = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}", null, true, true, true)[$row];
            $clean = array_map(
                fn ($value) => is_null($value) ? '' : trim((string) $value),
                array_values($values),
            );

            echo "{$row}: ".json_encode($clean, JSON_UNESCAPED_UNICODE)."\n";
        }
    }
}
