<?php
namespace Util;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter
{
    // 1️⃣ Build spreadsheet from array of associative arrays
    public static function buildSpreadsheet(array $data): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if (!empty($data)) {
            // Write headers
            $sheet->fromArray(array_keys($data[0]), null, 'A1');
            // Write rows
            $sheet->fromArray($data, null, 'A2');

            // Make headers bold
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')
                  ->getFont()->setBold(true);

            // Auto-size columns
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        return $spreadsheet;
    }

    // 2️⃣ Create a writer from a spreadsheet
    public static function createXlsxWriter(Spreadsheet $spreadsheet): Xlsx
    {
        return new Xlsx($spreadsheet);
    }

    // 3️⃣ Optional: return file as binary blob
    public static function getXlsxBinary(array $data): string
    {
        $spreadsheet = self::buildSpreadsheet($data);

        $writer = self::createXlsxWriter($spreadsheet);

        ob_start();                     // start buffer
        $writer->save('php://output');  // write XLSX to buffer
        $binary = ob_get_clean();       // get buffer contents

        return $binary;
    }

}
