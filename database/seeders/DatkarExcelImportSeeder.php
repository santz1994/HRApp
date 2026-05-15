<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DatkarExcelImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Imports real employee data directly from DATKAR APRIL 2026.xlsx
     */
    public function run(): void
    {
        $excelFile = base_path('DATKAR APRIL 2026.xlsx');
        
        if (!file_exists($excelFile)) {
            $this->command->error("❌ Excel file not found: {$excelFile}");
            return;
        }

        // Clear existing employees
        Employee::query()->delete();
        
        $this->command->info("📂 Importing employee data directly from DATKAR APRIL 2026.xlsx...\n");
        
        try {
            $spreadsheet = IOFactory::load($excelFile);
            $sheet = $spreadsheet->getSheetByName('WORKING (OKE)');
            
            $this->command->line("✓ Sheet loaded: WORKING (OKE)");
            
            $rows = $sheet->toArray(null, true, true, true);
            $totalRows = count($rows);
            
            $this->command->line("📊 Total rows in sheet: {$totalRows}\n");
            
            // Skip first 3 rows (title, date, headers) and process from row 4
            $successCount = 0;
            $failedCount = 0;
            $errors = [];
            
            for ($i = 4; $i <= $totalRows; $i++) {
                try {
                    $row = $rows[$i];
                    
                    // Skip empty rows
                    if (empty($row['A']) || strpos($row['A'], '=') !== false) {
                        continue;
                    }
                    
                    // Extract columns (A=NO, B=NIK, C=NO KTP, D=NAME, E=DEPT, F=JABATAN, etc.)
                    $nik = trim($row['B'] ?? '');
                    $noKtp = trim($row['C'] ?? '');
                    $nama = trim($row['D'] ?? '');
                    $dept = trim($row['E'] ?? '');
                    $jabatan = trim($row['F'] ?? '');
                    $tempatLahir = trim($row['G'] ?? '');
                    $tglMasuk = $row['H'] ?? null;
                    $tglLahir = $row['I'] ?? null;
                    $jenisKelamin = trim($row['N'] ?? '');
                    $deptOnLine = trim($row['O'] ?? '');
                    $deptOnLineAwal = trim($row['P'] ?? '');
                    $statusPkwtt = trim($row['Q'] ?? '');
                    $statusKeluarga = trim($row['R'] ?? '');
                    $pendidikan = trim($row['U'] ?? '');
                    $alamat = trim($row['V'] ?? '');
                    
                    // Skip if essential fields are missing or contain formulas
                    if (empty($nama) || empty($nik) || strpos($nik, '=') !== false) {
                        continue;
                    }
                    
                    // Parse dates
                    $tanggalMasuk = $this->parseExcelDate($tglMasuk) ?? '2026-01-01';
                    $tanggalLahir = $this->parseExcelDate($tglLahir) ?? '1990-01-01';
                    
                    // Map gender: P=Perempuan (F), L=Laki-laki (M)
                    $gender = strtoupper($jenisKelamin) === 'L' ? 'M' : 'F';
                    
                    // Normalize status
                    $statusPkwtt = strtoupper($statusPkwtt);
                    if (!in_array($statusPkwtt, ['TETAP', 'KONTRAK', 'HARIAN', 'MAGANG'])) {
                        $statusPkwtt = 'TETAP';
                    }
                    
                    // Normalize family status
                    $sk = strtoupper(trim($statusKeluarga));
                    if (strpos($sk, 'K') === 0) {
                        $statusKeluarga = 'K';
                    } elseif (in_array($sk, ['TK/0', 'TK', 'T/0'])) {
                        $statusKeluarga = 'TK';
                    } else {
                        $statusKeluarga = 'TK';
                    }
                    
                    // Create employee
                    Employee::create([
                        'nik' => $nik,
                        'no_ktp' => $noKtp,
                        'nama' => $nama,
                        'department' => $dept ?: 'GENERAL',
                        'jabatan' => $jabatan ?: 'KARYAWAN',
                        'tempat_lahir' => $tempatLahir ?: 'INDONESIA',
                        'tanggal_lahir' => $tanggalLahir,
                        'tanggal_masuk' => $tanggalMasuk,
                        'jenis_kelamin' => $gender,
                        'dept_on_line' => $deptOnLine ?: $dept,
                        'dept_on_line_awal' => $deptOnLineAwal ?: $dept,
                        'status_pkwtt' => $statusPkwtt,
                        'status_keluarga' => $statusKeluarga,
                        'jumlah_anak' => rand(0, 4),
                        'status_pajak' => $statusKeluarga === 'K' ? 'BUKAN PENERIMA' : 'PENERIMA',
                        'pendidikan' => $pendidikan ?: 'SMA',
                        'alamat_ktp' => $alamat ?: 'ALAMAT TIDAK TERSEDIA',
                        'alamat_domisili' => $alamat ?: 'ALAMAT TIDAK TERSEDIA',
                        'dokumen_pendukung' => json_encode([
                            'foto_ktp' => '/documents/ktp/' . $nik . '.jpg',
                            'foto_ijazah' => '/documents/ijazah/' . $nik . '.jpg'
                        ]),
                        'data_kepribadian' => json_encode([
                            'mbti' => $this->randomMBTI(),
                            'disc' => $this->randomDISC()
                        ]),
                        'ai_metrics' => json_encode([
                            'turnover_risk' => round(rand(3, 65) / 100, 2),
                            'performance_score' => round(rand(72, 95) / 10, 1)
                        ])
                    ]);
                    
                    $successCount++;
                    
                    if ($successCount % 5 == 0) {
                        $this->command->line("  ✓ Processed {$successCount} employees...");
                    }
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Row {$i}: " . $e->getMessage();
                }
            }
            
            $this->command->info("\n✅ IMPORT COMPLETE!");
            $this->command->line("   ✓ Successfully imported: {$successCount}");
            if ($failedCount > 0) {
                $this->command->line("   ✗ Failed: {$failedCount}");
            }
            
            if (!empty($errors) && count($errors) <= 5) {
                $this->command->warn("\n⚠️  Errors encountered:");
                foreach ($errors as $error) {
                    $this->command->line("   - {$error}");
                }
            }
            
        } catch (\Exception $e) {
            $this->command->error("❌ Import failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Parse Excel date format to Y-m-d
     */
    private function parseExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        // If it's a DateTime object (from PhpSpreadsheet)
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }
        
        // If it's an Excel serial number
        if (is_numeric($value) && $value > 0) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Try parsing as string
        if (is_string($value)) {
            $value = trim($value);
            $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
            foreach ($formats as $format) {
                try {
                    $parsed = \DateTime::createFromFormat($format, $value);
                    if ($parsed !== false) {
                        return $parsed->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get random MBTI type
     */
    private function randomMBTI(): string
    {
        $types = ['ESTJ', 'ISTJ', 'ESFJ', 'ISFJ', 'ESTP', 'ISTP', 'ESFP', 'ISFP',
                  'ENTJ', 'INTJ', 'ENFJ', 'INFJ', 'ENTP', 'INTP', 'ENFP', 'INFP'];
        return $types[array_rand($types)];
    }
    
    /**
     * Get random DISC type
     */
    private function randomDISC(): string
    {
        $types = ['D', 'I', 'S', 'C'];
        return $types[array_rand($types)];
    }
}
