<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ayuda a los controladores a generar un archivo .xlsx de descarga a partir
 * de encabezados + filas, con el mismo espíritu simple que los métodos
 * pdf() existentes (una consulta, una vista/armado de tabla, una descarga).
 *
 * Uso típico dentro de un controlador:
 *
 *   $rows = $doctors->map(fn ($doctor) => [$doctor->id, $doctor->person->name, ...]);
 *   return $this->streamExcel('doctores.xlsx', ['#', 'Nombre', ...], $rows);
 */
trait ExportsExcel
{
    protected function streamExcel(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        $lastColumn = $sheet->getHighestColumn();

        $headerRange = 'A1:' . $lastColumn . '1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(array_values($row), null, 'A' . $rowNumber);
            $rowNumber++;
        }

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Formatea de forma segura una fecha que puede venir como string plano
     * (columnas como 'sale_date' o 'date' que no están casteadas a Carbon
     * en el modelo) o ya como instancia de Carbon.
     */
    protected function formatDate($value, string $format = 'd/m/Y'): string
    {
        if (! $value) {
            return '';
        }

        return Carbon::parse($value)->format($format);
    }
}
