<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Collection;

class ExcelImportService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Import employees from array (parsed Excel data)
     * Expects array with keys: nik, no_ktp, nama, department, jabatan, tempat_lahir, 
     *                          tanggal_masuk, tanggal_lahir, jenis_kelamin, dept_on_line,
     *                          dept_on_line_awal, status_pkwtt, status_keluarga, pendidikan, alamat
     */
    public function importFromArray(array $data): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'imported_ids' => []
        ];

        foreach ($data as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['nik'])) {
                    continue;
                }

                // Normalize data
                $employeeData = $this->normalizeRowData($row);

                // Validate required fields
                if (!$this->validateEmployeeData($employeeData)) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'message' => 'Missing required fields: nik, nama, tanggal_masuk, tanggal_lahir'
                    ];
                    continue;
                }

                // Upsert: Update if NIK exists, otherwise create
                $employee = Employee::updateOrCreate(
                    ['nik' => $employeeData['nik']],
                    $employeeData
                );

                $results['success']++;
                $results['imported_ids'][] = $employee->id;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 1,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Normalize row data from Excel
     */
    private function normalizeRowData(array $row): array
    {
        return [
            'nik' => trim($row['nik'] ?? $row['NIK'] ?? ''),
            'no_ktp' => trim($row['no_ktp'] ?? $row['NO_KTP'] ?? ''),
            'nama' => trim($row['nama'] ?? $row['NAMA'] ?? $row['name'] ?? ''),
            'department' => trim($row['department'] ?? $row['DEPT'] ?? ''),
            'jabatan' => trim($row['jabatan'] ?? $row['JABATAN'] ?? ''),
            'tempat_lahir' => trim($row['tempat_lahir'] ?? $row['TEMPAT LAHIR'] ?? ''),
            'tanggal_masuk' => $this->parseDate($row['tanggal_masuk'] ?? $row['TANGGAL MASUK'] ?? null),
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? $row['TANGGAL LAHIR'] ?? null),
            'jenis_kelamin' => trim($row['jenis_kelamin'] ?? $row['JENIS KELA'] ?? ''),
            'dept_on_line' => trim($row['dept_on_line'] ?? $row['DEPT ON LINE'] ?? ''),
            'dept_on_line_awal' => trim($row['dept_on_line_awal'] ?? $row['DEPT ON LINE awal'] ?? ''),
            'status_pkwtt' => trim($row['status_pkwtt'] ?? $row['STATUS PKWTT'] ?? 'TETAP'),
            'status_keluarga' => trim($row['status_keluarga'] ?? $row['STATUS KELUARGA'] ?? ''),
            'pendidikan' => trim($row['pendidikan'] ?? $row['PENDIDIKAN'] ?? ''),
            'alamat' => trim($row['alamat'] ?? $row['ALAMAT'] ?? ''),
        ];
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($date)
    {
        if (!$date) {
            return null;
        }

        try {
            // Handle Excel date numbers (serial date)
            if (is_numeric($date)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
                return $date->format('Y-m-d');
            }

            // Try common date formats
            foreach (['d-M-Y', 'd-m-Y', 'd/m/Y', 'Y-m-d', 'm/d/Y'] as $format) {
                $parsed = \DateTime::createFromFormat($format, (string)$date);
                if ($parsed) {
                    return $parsed->format('Y-m-d');
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate required fields
     */
    private function validateEmployeeData(array $data): bool
    {
        return !empty($data['nik']) &&
               !empty($data['nama']) &&
               !empty($data['tanggal_masuk']) &&
               !empty($data['tanggal_lahir']);
    }
}
