<?php
/**
 * Test Script: Read Excel file and display structure
 * This helps understand the exact columns and data from DATKAR APRIL 2026.xlsx
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'DATKAR APRIL 2026.xlsx';

if (!file_exists($filePath)) {
    echo "❌ File not found: $filePath\n";
    exit(1);
}

echo "📊 Reading Excel File: $filePath\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Get dimensions
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    // Convert column letter to number (A=1, B=2, etc.)
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Coordinate\Coordinate::columnLetterToColumnIndex($highestColumn);
    
    echo "📈 File Stats:\n";
    echo "   • Total Rows: $highestRow\n";
    echo "   • Total Columns: $highestColumnIndex\n";
    echo "   • Column Range: A - $highestColumn\n\n";
    
    // Get headers
    echo "📋 Column Headers (Row 1):\n";
    $headers = [];
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $cell = $worksheet->getCellByColumnAndRow($col, 1);
        $header = trim($cell->getValue() ?? '');
        $headers[$col] = $header;
        echo "   [$col] " . str_pad($header, 30) . "\n";
    }
    
    echo "\n📊 Sample Data (First 3 Employees):\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    
    for ($row = 2; $row <= min(4, $highestRow); $row++) {
        echo "\n🔹 Row $row:\n";
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $header = $headers[$col] ?? "Col_$col";
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $value = $cell->getValue();
            
            // Format dates nicely
            if ($cell->getDataType() == 'd' || strtotime((string)$value) !== false) {
                if (is_numeric($value)) {
                    $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                }
            }
            
            echo "   • $header: " . (is_null($value) ? '(empty)' : $value) . "\n";
        }
    }
    
    echo "\n\n✅ Excel file is valid and readable!\n";
    echo "📌 Next: Use /api/employees/import-export/import to import this file.\n";
    
} catch (\Exception $e) {
    echo "❌ Error reading Excel file:\n";
    echo "   " . $e->getMessage() . "\n";
    exit(1);
}
