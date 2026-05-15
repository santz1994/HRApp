<?php
/**
 * Import CSV ke Database
 * Usage: php import_csv.php "DATKAR APRIL 2026_converted.csv"
 */

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

$csvFile = $argv[1] ?? "DATKAR APRIL 2026_converted.csv";

if (!file_exists($csvFile)) {
    echo "❌ File not found: $csvFile\n";
    exit(1);
}

echo "📂 Importing from CSV: $csvFile\n\n";

try {
    $handle = fopen($csvFile, 'r');
    
    // Read header
    $headers = fgetcsv($handle);
    echo "Headers found: " . count($headers) . "\n";
    echo "Sample: " . implode(", ", array_slice($headers, 0, 5)) . "...\n\n";

    // Map CSV columns to database columns
    $columnMap = [
        'No' => null,
        'NIK' => 'nik',
        'No. KTP' => 'no_ktp',
        'Nama' => 'nama',
        'Tempat Lahir' => 'tempat_lahir',
        'Tgl Lahir' => 'tanggal_lahir',
        'Tgl Masuk' => 'tanggal_masuk',
        'L/P' => 'jenis_kelamin',
        'Depart' => 'department',
        'Jabatan' => 'jabatan',
        'Dept On Line Awal' => 'dept_on_line_awal',
        'Dept On Line' => 'dept_on_line',
        'Status Kerja' => 'status_pkwtt',
        'Status Keluarga' => 'status_keluarga',
        'Jml Anak' => 'jumlah_anak',
        'Pendidikan' => 'pendidikan',
        'Alamat' => 'alamat_ktp',
    ];

    echo "Processing rows...\n";
    
    $rowCount = 0;
    $successCount = 0;
    $failedCount = 0;
    $errors = [];
    
    while (($row = fgetcsv($handle)) !== false) {
        $rowCount++;
        
        try {
            // Combine headers with row data
            $data = array_combine($headers, $row);
            
            // Map data
            $employeeData = [];
            foreach ($columnMap as $csvCol => $dbCol) {
                if ($dbCol && isset($data[$csvCol])) {
                    $value = trim($data[$csvCol]);
                    
                    // Normalize values
                    if ($dbCol === 'tanggal_lahir' || $dbCol === 'tanggal_masuk') {
                        // Convert Excel date format
                        if ($value) {
                            try {
                                $date = \DateTime::createFromFormat('d/m/Y', $value) 
                                    ?: \DateTime::createFromFormat('Y-m-d', $value);
                                if ($date) {
                                    $value = $date->format('Y-m-d');
                                }
                            } catch (\Exception $e) {
                                // Keep original value, let model validation handle it
                            }
                        }
                    } elseif ($dbCol === 'jenis_kelamin') {
                        $value = strtoupper(substr($value, 0, 1)); // L or P
                    } elseif ($dbCol === 'status_pkwtt') {
                        $value = strtoupper($value);
                    } elseif ($dbCol === 'status_keluarga') {
                        $value = ucwords(strtolower($value));
                    } elseif ($dbCol === 'jumlah_anak') {
                        $value = intval($value) ?? 0;
                    }
                    
                    if (!empty($value)) {
                        $employeeData[$dbCol] = $value;
                    }
                }
            }
            
            // Required fields validation
            if (empty($employeeData['nik']) || empty($employeeData['nama'])) {
                throw new \Exception("NIK or Nama is empty");
            }
            
            // Upsert: Update if NIK exists, else create
            if (!empty($employeeData['nik'])) {
                $employee = Employee::updateOrCreate(
                    ['nik' => $employeeData['nik']],
                    $employeeData
                );
                $successCount++;
            }
            
            if ($rowCount % 50 == 0) {
                echo ".";
                flush();
            }
            
        } catch (\Exception $e) {
            $failedCount++;
            $errors[] = [
                'row' => $rowCount,
                'error' => $e->getMessage()
            ];
            
            if ($failedCount <= 5) {
                echo "\n⚠️ Row $rowCount: " . $e->getMessage();
            }
        }
    }
    
    fclose($handle);
    
    echo "\n\n";
    echo "✅ Import Complete!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Total rows: $rowCount\n";
    echo "Success: $successCount\n";
    echo "Failed: $failedCount\n";
    
    if (!empty($errors)) {
        echo "\nFirst 5 errors:\n";
        foreach (array_slice($errors, 0, 5) as $err) {
            echo "  Row {$err['row']}: {$err['error']}\n";
        }
    }
    
    // Verify in database
    $total = Employee::count();
    echo "\n📊 Total employees in database: $total\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
