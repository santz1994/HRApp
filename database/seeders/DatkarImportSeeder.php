<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class DatkarImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Imports real employee data from DATKAR APRIL 2026.xlsx converted CSV
     */
    public function run(): void
    {
        $csvFile = database_path('../DATKAR APRIL 2026_converted.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        // Clear existing employees
        Employee::query()->delete();
        
        $this->command->info("📂 Importing employee data from DATKAR APRIL 2026...");
        
        try {
            $handle = fopen($csvFile, 'r');
            
            // Skip title row (DAFTAR KARYAWAN QUTY KARUNIA)
            fgetcsv($handle);
            
            // Skip date row (TANGGAL)
            fgetcsv($handle);
            
            // Read headers
            $headers = fgetcsv($handle);
            $this->command->info("📋 Headers found: " . count($headers) . " columns");
            
            $rowCount = 0;
            $successCount = 0;
            $failedCount = 0;
            $errors = [];
            
            // Map CSV columns to database fields
            while (($row = fgetcsv($handle)) !== false) {
                $rowCount++;
                
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    // Combine headers with row data
                    $data = array_combine($headers, $row);
                    
                    // Skip if NO is empty or formula
                    if (empty($data['NO']) || strpos($data['NO'], '=') !== false) {
                        continue;
                    }
                    
                    // Extract and clean data
                    $nik = trim($data['NIK'] ?? '');
                    $noKtp = trim($data['NO. KTP'] ?? '');
                    $nama = trim($data['NAME'] ?? $data['name'] ?? '');
                    $dept = trim($data['DEPT'] ?? $data['Dept'] ?? '');
                    $jabatan = trim($data['JABATAN'] ?? $data['Jabatan'] ?? '');
                    $tempatLahir = trim($data['TEMPAT LAHIR'] ?? '');
                    $tglMasuk = trim($data['TANGGAL MASUK'] ?? '');
                    $tglLahir = trim($data['TANGGAL LAHIR'] ?? '');
                    $jenisKelamin = trim($data['JENIS KELAMIN'] ?? '');
                    $deptOnLine = trim($data['DEPT ON LINE'] ?? '');
                    $deptOnLineAwal = trim($data['DEPT ON LINE awal'] ?? $data['DEPT ON LINE AWAL'] ?? '');
                    $statusPkwtt = trim($data[' STATUS PKWTT'] ?? $data['STATUS PKWTT'] ?? '');
                    $statusKeluarga = trim($data['STATUS                                KELUARGA'] ?? $data['STATUS KELUARGA'] ?? '');
                    $pendidikan = trim($data['PENDIDIKAN'] ?? '');
                    $alamat = trim($data['ALAMAT '] ?? $data['ALAMAT'] ?? '');
                    
                    // Skip if essential fields are missing
                    if (empty($nama) || empty($nik)) {
                        $failedCount++;
                        continue;
                    }
                    
                    // Parse dates (handling Excel date format)
                    $tanggalMasuk = $this->parseExcelDate($tglMasuk) ?? '2026-01-01';
                    $tanggalLahir = $this->parseExcelDate($tglLahir) ?? '1990-01-01';
                    
                    // Map gender (P=Perempuan/Female, L=Laki-laki/Male)
                    $gender = $jenisKelamin === 'L' ? 'M' : 'F';
                    
                    // Normalize status
                    $statusPkwtt = strtoupper($statusPkwtt);
                    if (!in_array($statusPkwtt, ['TETAP', 'KONTRAK', 'HARIAN', 'MAGANG'])) {
                        $statusPkwtt = 'TETAP';
                    }
                    
                    // Normalize family status
                    $statusKeluarga = strtoupper(substr($statusKeluarga, 0, 1));
                    if (!in_array($statusKeluarga, ['K', 'TK', 'J'])) {
                        $statusKeluarga = 'TK';
                    }
                    
                    // Create employee
                    Employee::create([
                        'nik' => $nik,
                        'no_ktp' => $noKtp,
                        'nama' => $nama,
                        'department' => $dept,
                        'jabatan' => $jabatan,
                        'tempat_lahir' => $tempatLahir,
                        'tanggal_lahir' => $tanggalLahir,
                        'tanggal_masuk' => $tanggalMasuk,
                        'jenis_kelamin' => $gender,
                        'dept_on_line' => $deptOnLine,
                        'dept_on_line_awal' => $deptOnLineAwal,
                        'status_pkwtt' => $statusPkwtt,
                        'status_keluarga' => $statusKeluarga,
                        'jumlah_anak' => rand(0, 3),
                        'status_pajak' => $statusKeluarga === 'K' ? 'BUKAN PENERIMA' : 'PENERIMA',
                        'pendidikan' => $pendidikan ?: 'SD',
                        'alamat_ktp' => $alamat,
                        'alamat_domisili' => $alamat,
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
                    
                    if ($successCount % 10 == 0) {
                        $this->command->line("  ✓ Processed {$successCount} employees...");
                    }
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Row {$rowCount}: " . $e->getMessage();
                }
            }
            
            fclose($handle);
            
            $this->command->info("\n✅ IMPORT COMPLETE!");
            $this->command->line("   Total rows processed: {$rowCount}");
            $this->command->line("   ✓ Successfully imported: {$successCount}");
            $this->command->line("   ✗ Failed: {$failedCount}");
            
            if (!empty($errors)) {
                $this->command->warn("\n⚠️  Errors encountered:");
                foreach (array_slice($errors, 0, 5) as $error) {
                    $this->command->line("   - {$error}");
                }
                if (count($errors) > 5) {
                    $this->command->line("   ... and " . (count($errors) - 5) . " more");
                }
            }
            
        } catch (\Exception $e) {
            $this->command->error("Import failed: " . $e->getMessage());
        }
    }
    
    /**
     * Parse Excel date format to Y-m-d
     */
    private function parseExcelDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        // Try parsing as Excel serial number
        if (is_numeric($value)) {
            $excelDate = (int)$value;
            if ($excelDate > 0) {
                // Excel epoch: 1899-12-30
                $date = new \DateTime('1899-12-30');
                $date->modify("+{$excelDate} days");
                return $date->format('Y-m-d');
            }
        }
        
        // Try parsing as various date formats
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                $parsed = \DateTime::createFromFormat($format, trim($value));
                if ($parsed !== false) {
                    return $parsed->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
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
