<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DatkarExcelImportSeeder extends Seeder
{
    /**
     * Import data karyawan dari Book1.xlsx ke database.
     * Sesuai Project.md:
     * - Kolom: nik_karyawan, no_ktp, nama_lengkap, department_id, position_id, dll
     * - Tanggal dalam format serial Excel, perlu dikonversi ke Y-m-d
     * - Upsert berdasarkan nik_karyawan untuk cegah duplikasi
     */
    public function run(): void
    {
        $filePath = base_path('Book1.xlsx');

        if (!file_exists($filePath)) {
            $this->command?->warn('File Book1.xlsx tidak ditemukan di: ' . $filePath);
            return;
        }

        $this->command?->info('Memproses Book1.xlsx...');

        // Read Excel with chunked approach to save memory
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $this->command?->info("Total baris: {$highestRow}");

        // Mapping departments & positions
        $departments = [];
        $positions = [];
        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        // Start from row 3 (row 1 = headers, row 2 = sample data)
        for ($row = 3; $row <= $highestRow; $row++) {
            try {
                $no = $sheet->getCell('A' . $row)->getValue();
                $nik = trim((string) $sheet->getCell('B' . $row)->getValue());
                $noKtp = trim((string) $sheet->getCell('C' . $row)->getValue());
                $nama = trim((string) $sheet->getCell('D' . $row)->getValue());
                $deptName = trim((string) $sheet->getCell('E' . $row)->getValue());
                $jabatanName = trim((string) $sheet->getCell('F' . $row)->getValue());
                $tempatLahir = trim((string) $sheet->getCell('G' . $row)->getValue());
                $tglMasukExcel = $sheet->getCell('H' . $row)->getValue();
                $tglLahirExcel = $sheet->getCell('I' . $row)->getValue();
                $jenisKelamin = trim((string) $sheet->getCell('N' . $row)->getValue());
                $deptOnLine = trim((string) $sheet->getCell('O' . $row)->getValue());
                $deptOnLineAwal = trim((string) $sheet->getCell('P' . $row)->getValue());
                $statusPKWTT = strtoupper(trim((string) $sheet->getCell('Q' . $row)->getValue()));
                $statusKeluarga = trim((string) $sheet->getCell('R' . $row)->getValue());
                $pendidikan = trim((string) $sheet->getCell('T' . $row)->getValue());
                $alamat = trim((string) $sheet->getCell('U' . $row)->getValue());

                // Skip rows without NIK
                if (empty($nik) || $nik === 'NIK') {
                    $skipCount++;
                    continue;
                }

                // Skip rows without name
                if (empty($nama) || $nama === 'NAME' || $nama === 'NAMA') {
                    $skipCount++;
                    continue;
                }

                // Get or create department
                if (!empty($deptName) && !isset($departments[$deptName])) {
                    $dept = Department::firstOrCreate(['name' => $deptName]);
                    $departments[$deptName] = $dept->id;
                }
                $departmentId = $departments[$deptName] ?? null;

                // Get or create position
                if (!empty($jabatanName) && !isset($positions[$jabatanName])) {
                    $pos = Position::firstOrCreate(['name' => $jabatanName]);
                    $positions[$jabatanName] = $pos->id;
                }
                $positionId = $positions[$jabatanName] ?? null;

                // Convert Excel serial date to Y-m-d
                $tglMasuk = $this->excelDateToDate($tglMasukExcel);
                $tglLahir = $this->excelDateToDate($tglLahirExcel);

                // Normalize jenis kelamin
                $jk = strtoupper(substr($jenisKelamin, 0, 1));
                if (!in_array($jk, ['L', 'P'])) {
                    $jk = null;
                }

                // Normalize status PKWTT
                $validStatus = ['TETAP', 'KONTRAK', 'HARIAN', 'MAGANG'];
                if (!in_array($statusPKWTT, $validStatus)) {
                    $statusPKWTT = 'KONTRAK';
                }

                // Normalize status keluarga
                $statusKeluargaNorm = match(true) {
                    str_contains(strtoupper($statusKeluarga), 'KAWIN') && !str_contains(strtoupper($statusKeluarga), 'CERAI') => 'Kawin',
                    str_contains(strtoupper($statusKeluarga), 'CERAI HIDUP') => 'Cerai Hidup',
                    str_contains(strtoupper($statusKeluarga), 'CERAI MATI') => 'Cerai Mati',
                    default => 'Lajang',
                };

                // Get jumlah_anak from status_pajak column (R)
                $jumlahAnak = 0;
                if (preg_match('/\/(\d+)/', $statusKeluarga, $matches)) {
                    $jumlahAnak = (int) $matches[1];
                }

                // Pastikan no_ktp unik
                $finalKtp = $noKtp ?: $nik . '-0';
                $existingWithKtp = Employee::where('no_ktp', $finalKtp)->first();
                if ($existingWithKtp && $existingWithKtp->nik_karyawan !== $nik) {
                    // Jika KTP sudah dipakai karyawan lain, buat unique suffix
                    $finalKtp = $finalKtp . '-' . substr($nik, -4);
                }

                $employeeData = [
                    'nik_karyawan' => $nik,
                    'no_ktp' => $finalKtp,
                    'nama_lengkap' => $nama,
                    'tempat_lahir' => $tempatLahir !== '-' ? $tempatLahir : null,
                    'tanggal_lahir' => $tglLahir,
                    'tanggal_masuk_kerja' => $tglMasuk,
                    'department_id' => $departmentId,
                    'position_id' => $positionId,
                    'jenis_kelamin' => $jk,
                    'status_pkwtt' => $statusPKWTT,
                    'status_keluarga' => $statusKeluargaNorm,
                    'jumlah_anak' => $jumlahAnak,
                    'pendidikan' => $pendidikan !== '-' ? $pendidikan : null,
                    'alamat_ktp' => $alamat !== '-' ? $alamat : null,
                    'alamat_domisili' => $alamat !== '-' ? $alamat : null,
                ];

                // Upsert: update jika nik_karyawan sudah ada
                Employee::updateOrCreate(
                    ['nik_karyawan' => $nik],
                    $employeeData
                );

                $successCount++;

                if ($successCount % 100 === 0) {
                    $this->command?->info("  ... {$successCount} baris diproses");
                }
            } catch (\Exception $e) {
                $errorCount++;
                // Per-row error handling - skip baris error, lanjut baris berikutnya
                if ($errorCount <= 10) {
                    $this->command?->warn("  Skip baris {$row} ({$nama}): " . $e->getMessage());
                }
                Log::warning('Baris import gagal', [
                    'row' => $row,
                    'nik' => $nik,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->command?->info("Import selesai!");
        $this->command?->info("  Berhasil: {$successCount}");
        $this->command?->info("  Dilewati: {$skipCount}");
        $this->command?->info("  Error: {$errorCount}");
        $this->command?->info("  Departments: " . count($departments));
        $this->command?->info("  Positions: " . count($positions));
    }

    /**
     * Konversi Excel serial date ke format Y-m-d.
     * Excel date: 1 = 1 Jan 1900
     */
    private function excelDateToDate($excelDate): ?string
    {
        if (!$excelDate || !is_numeric($excelDate)) {
            return null;
        }

        try {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}