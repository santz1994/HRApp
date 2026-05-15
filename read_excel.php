<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);

$sheet = $reader->load('Book1.xlsx')->getActiveSheet();
$highestRow = $sheet->getHighestRow();

echo "Total rows: {$highestRow}\n\n";

// Read header row using rangeToArray
$headerData = $sheet->rangeToArray('A1:X1', null, true, false, false);
if (!empty($headerData[1])) {
    echo "HEADERS: " . json_encode(array_values($headerData[1]), JSON_UNESCAPED_UNICODE) . "\n\n";
}

// Read first 5 data rows
for ($row = 2; $row <= min(6, $highestRow); $row++) {
    $rowLetter = 'A';
    $rowData = $sheet->rangeToArray("{$rowLetter}{$row}:X{$row}", null, true, false, false);
    if (!empty($rowData[$row])) {
        echo "ROW {$row}: " . json_encode(array_values($rowData[$row]), JSON_UNESCAPED_UNICODE) . "\n";
    }
}