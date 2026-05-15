<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'DATKAR APRIL 2026.xlsx';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getSheetByName('WORKING (OKE)');

echo "Sheet: WORKING (OKE)\n";
echo "Rows: " . $sheet->getHighestRow() . "\n";
echo "Cols: " . $sheet->getHighestColumn() . "\n";
echo "\nHeaders:\n";

// Get headers
for ($col = 1; $col <= $sheet->getHighestColumn(); $col++) {
    $cell = $sheet->getCellByColumnAndRow($col, 1);
    echo $col . ". " . $cell->getValue() . "\n";
}

echo "\nSample Data (Rows 2-4):\n";
for ($row = 2; $row <= 4; $row++) {
    echo "Row $row: ";
    for ($col = 1; $col <= min(8, $sheet->getHighestColumn()); $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, $row);
        $val = $cell->getValue();
        echo (is_object($val) ? $val->__toString() : $val) . " | ";
    }
    echo "\n";
}

echo "\nTotal data rows: " . ($sheet->getHighestRow() - 1) . "\n";
