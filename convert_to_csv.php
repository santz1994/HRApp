<?php
/**
 * Convert XLSX to CSV untuk data yang sangat besar
 * Usage: php convert_to_csv.php "DATKAR APRIL 2026.xlsx" "WORKING (OKE)"
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

if ($argc < 3) {
    echo "Usage: php convert_to_csv.php <input_file> <sheet_name>\n";
    echo "Example: php convert_to_csv.php 'DATKAR APRIL 2026.xlsx' 'WORKING (OKE)'\n";
    exit(1);
}

$inputFile = $argv[1];
$sheetName = $argv[2];
$outputFile = pathinfo($inputFile, PATHINFO_FILENAME) . "_converted.csv";

if (!file_exists($inputFile)) {
    echo "❌ File not found: $inputFile\n";
    exit(1);
}

echo "📂 Converting XLSX to CSV...\n";
echo "Input: $inputFile\n";
echo "Sheet: $sheetName\n";
echo "Output: $outputFile\n\n";

try {
    // Use streaming reader untuk efisiensi memory
    $reader = new Xlsx();
    $reader->setReadDataOnly(true);
    
    echo "Loading spreadsheet...";
    $spreadsheet = $reader->load($inputFile);
    echo " ✓\n";

    // Get sheet by name
    echo "Finding sheet '$sheetName'...";
    $sheet = $spreadsheet->getSheetByName($sheetName);
    echo " ✓\n";

    $totalRows = $sheet->getHighestRow();
    echo "Total rows: $totalRows\n\n";

    // Write to CSV dengan streaming
    echo "Writing to CSV...";
    $csvFile = fopen($outputFile, 'w');
    
    $rowCount = 0;
    foreach ($sheet->getRowIterator() as $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            $value = $cell->getValue();
            
            // Handle dates
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d');
            }
            // Escape quotes
            $value = str_replace('"', '""', $value);
            $rowData[] = $value;
        }
        
        fputcsv($csvFile, $rowData);
        
        $rowCount++;
        if ($rowCount % 100 == 0) {
            echo ".";
            flush();
        }
    }
    
    fclose($csvFile);
    echo " ✓\n\n";

    echo "✅ SUCCESS!\n";
    echo "Output file: $outputFile\n";
    echo "Total rows written: $rowCount\n";
    echo "File size: " . round(filesize($outputFile) / 1024 / 1024, 2) . " MB\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
