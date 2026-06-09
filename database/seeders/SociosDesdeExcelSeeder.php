<?php

namespace Database\Seeders;

use App\Models\Socio;
use Database\Seeders\Concerns\ReadsSeedExcel;
use Illuminate\Database\Seeder;

class SociosDesdeExcelSeeder extends Seeder
{
    use ReadsSeedExcel;

    public function run(): void
    {
        $sheet = $this->workbook('Nomina Socios.xlsx')->getSheetByName('Socios');
        $usedCodes = [];

        for ($row = 4; $row <= $sheet->getHighestDataRow(); $row++) {
            $nombre = $this->text($sheet, "B{$row}");

            if ($nombre === '') {
                continue;
            }

            $numeroCasa = $this->text($sheet, "E{$row}");
            $codigoPago = $this->uniqueCode($numeroCasa !== '' ? "CASA-{$numeroCasa}" : "SOCIO-{$row}", $usedCodes);

            Socio::updateOrCreate([
                'codigo_pago' => $codigoPago,
            ], [
                'nombre' => $nombre,
                'rut' => $this->text($sheet, "G{$row}") ?: null,
                'direccion' => $this->text($sheet, "D{$row}") ?: null,
                'numero_casa' => $numeroCasa ?: null,
                'sector' => $this->text($sheet, "F{$row}") ?: null,
                'telefono' => $this->text($sheet, "I{$row}") ?: null,
                'email' => null,
                'fecha_incorporacion' => $this->dateFromCell($sheet, "C{$row}"),
                'fecha_nacimiento' => $this->dateFromCell($sheet, "H{$row}"),
                'estado' => 'activo',
                'observaciones' => null,
                'datos_originales' => [
                    'archivo' => 'Nomina Socios.xlsx',
                    'hoja' => 'Socios',
                    'fila' => $row,
                    'valores' => $this->rowSnapshot($sheet, $row),
                ],
            ]);
        }
    }

    private function uniqueCode(string $baseCode, array &$usedCodes): string
    {
        $baseCode = mb_strtoupper(str_replace(' ', '-', $baseCode));
        $code = $baseCode;
        $suffix = 2;

        while (isset($usedCodes[$code])) {
            $code = "{$baseCode}-{$suffix}";
            $suffix++;
        }

        $usedCodes[$code] = true;

        return $code;
    }
}
