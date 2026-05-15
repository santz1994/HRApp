<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Import employees from array (parsed Excel data).
     * Menggunakan Upsert berdasarkan NIK/KTP untuk mencegah duplikasi (sesuai Project.md).
     */
    public function importFromArray(array $data): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'imported_ids' => [],
        ];

        foreach ($data as $index => $row) {
            try {
                // Skip baris kosong
                if (empty($row['nik_karyawan']) && empty($row['nik'])) {
                    continue;
                }

                // Normalisasi data
                $employeeData = $this->normalizeRowData($row);

                // Validasi field wajib
                if (!$this->validateEmployeeData($employeeData)) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 2, // +2 karena header row + 0-indexed
                        'message' => 'Field wajib tidak lengkap: nik_karyawan, nama_lengkap, tanggal_masuk_kerja, tanggal_lahir',
                    ];
                    continue;
                }

                // Resolve department_id dari nama department
                if (!empty($employeeData['department_name'])) {
                    $dept = Department::where('name', $employeeData['department_name'])->first();
                    $employeeData['department_id'] = $dept?->id;
                    unset($employeeData['department_name']);
                }

                // Resolve position_id dari nama jabatan
                if (!empty($employeeData['position_name'])) {
                    $pos = Position::where('name', $employeeData['position_name'])->first();
                    $employeeData['position_id'] = $pos?->id;
                    unset($employeeData['position_name']);
                }

                // Upsert: Update jika NIK/KTP ada, jika tidak insert baru
                $employee = $this->employeeRepository->upsert($employeeData);

                $results['success']++;
                $results['imported_ids'][] = $employee->id;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Normalisasi data baris dari Excel.
     * Mendukung nama kolom lama dan baru.
     */
    private function normalizeRowData(array $row): array
    {
        return [
            'nik_karyawan' => trim($row['nik_karyawan'] ?? $row['NIK KARYAWAN'] ?? $row['nik'] ?? $row['NIK'] ?? ''),
            'no_ktp' => trim($row['no_ktp'] ?? $row['NO_KTP'] ?? $row['NO KTP'] ?? ''),
            'nama_lengkap' => trim($row['nama_lengkap'] ?? $row['NAMA LENGKAP'] ?? $row['nama'] ?? $row['NAMA'] ?? ''),
            'department_name' => trim($row['department'] ?? $row['DEPARTMENT'] ?? $row['DEPARTEMENT'] ?? $row['DEPT'] ?? ''),
            'position_name' => trim($row['jabatan'] ?? $row['JABATAN'] ?? $row['position'] ?? $row['POSITION'] ?? ''),
            'tempat_lahir' => trim($row['tempat_lahir'] ?? $row['TEMPAT LAHIR'] ?? ''),
            'tanggal_masuk_kerja' => $this->parseDate($row['tanggal_masuk_kerja'] ?? $row['TANGGAL MASUK KERJA'] ?? $row['tanggal_masuk'] ?? $row['TANGGAL MASUK'] ?? null),
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? $row['TANGGAL LAHIR'] ?? null),
            'jenis_kelamin' => strtoupper(trim($row['jenis_kelamin'] ?? $row['JENIS KELAMIN'] ?? '')),
            'status_pkwtt' => strtoupper(trim($row['status_pkwtt'] ?? $row['STATUS PKWTT'] ?? 'KONTRAK')),
            'status_keluarga' => ucwords(strtolower(trim($row['status_keluarga'] ?? $row['STATUS KELUARGA'] ?? 'Lajang'))),
            'jumlah_anak' => (int) ($row['jumlah_anak'] ?? $row['JUMLAH ANAK'] ?? 0),
            'pendidikan' => trim($row['pendidikan'] ?? $row['PENDIDIKAN'] ?? ''),
            'alamat_ktp' => trim($row['alamat_ktp'] ?? $row['ALAMAT KTP'] ?? $row['ALAMAT'] ?? ''),
            'alamat_domisili' => trim($row['alamat_domisili'] ?? $row['ALAMAT DOMISILI'] ?? ''),
        ];
    }

    /**
     * Parse tanggal dari berbagai format.
     */
    private function parseDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            // Handle Excel serial date
            if (is_numeric($date)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
                return $date->format('Y-m-d');
            }

            // Coba beberapa format tanggal umum
            foreach (['d-M-Y', 'd-m-Y', 'd/m/Y', 'Y-m-d', 'm/d/Y', 'd M Y'] as $format) {
                $parsed = \DateTime::createFromFormat($format, (string) $date);
                if ($parsed) {
                    return $parsed->format('Y-m-d');
                }
            }

            // Fallback ke strtotime
            $timestamp = strtotime((string) $date);
            if ($timestamp) {
                return date('Y-m-d', $timestamp);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validasi field wajib.
     */
    private function validateEmployeeData(array $data): bool
    {
        return !empty($data['nik_karyawan'])
            && !empty($data['nama_lengkap'])
            && !empty($data['tanggal_masuk_kerja'])
            && !empty($data['tanggal_lahir']);
    }
}