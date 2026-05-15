<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);

$sheet = $reader->load('Book1.xlsx')->getActiveSheet();
$highestRow = $sheet->getHighestRow();

echo "Total rows: {$highestRow}\n\n";

// Read header row (A-X only)
$headers = [];
for ($col = 'A'; $col <= 'X'; $col++) {
    $headers[] = $sheet->getCell($col . '1')->getValue();
}
echo "HEADERS: " . json_encode($headers, JSON_UNESCAPED_UNICODE) . "\n\n";

// Read first 10 data rows
for ($row = 2; $row <= min(11, $highestRow); $row++) {
    $rowData = [];
    for ($col = 'A'; $col <= 'X'; $col++) {
        $rowData[] = $sheet->getCell($col . $row)->getValue();
    }
    echo "ROW {$row}: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) . "\n";
}