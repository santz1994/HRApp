<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Get employees list with filters and pagination.
     */
    public function getEmployeesList(
        array $filters = [],
        string $sortBy = 'nama_lengkap',
        string $sortDir = 'asc',
        int $perPage = 50
    ): LengthAwarePaginator {
        return $this->employeeRepository->getEmployees(
            $filters,
            $sortBy,
            $sortDir,
            $perPage
        );
    }

    /**
     * Get employee details.
     */
    public function getEmployeeDetails(int $employeeId): ?Employee
    {
        return $this->employeeRepository->findById($employeeId);
    }

    /**
     * Create a new employee with validation.
     */
    public function createEmployee(array $data): Employee
    {
        $this->validateEmployeeData($data);

        if ($this->employeeRepository->findByNIK($data['nik_karyawan'])) {
            throw new \Exception('Karyawan dengan NIK ini sudah ada.');
        }

        if ($this->employeeRepository->findByKTP($data['no_ktp'])) {
            throw new \Exception('Karyawan dengan No. KTP ini sudah ada.');
        }

        return $this->employeeRepository->create($data);
    }

    /**
     * Update employee data with validation.
     */
    public function updateEmployee(int $employeeId, array $data): bool
    {
        $this->validateEmployeeData($data, false);

        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee) {
            throw new \Exception('Karyawan tidak ditemukan.');
        }

        // Check duplicate NIK (excluding current)
        if (isset($data['nik_karyawan']) && $data['nik_karyawan'] !== $employee->nik_karyawan) {
            if ($this->employeeRepository->findByNIK($data['nik_karyawan'])) {
                throw new \Exception('NIK sudah digunakan oleh karyawan lain.');
            }
        }

        // Check duplicate KTP (excluding current)
        if (isset($data['no_ktp']) && $data['no_ktp'] !== $employee->no_ktp) {
            if ($this->employeeRepository->findByKTP($data['no_ktp'])) {
                throw new \Exception('No. KTP sudah digunakan oleh karyawan lain.');
            }
        }

        return $this->employeeRepository->update($employeeId, $data);
    }

    /**
     * Delete employee.
     */
    public function deleteEmployee(int $employeeId): bool
    {
        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee) {
            throw new \Exception('Karyawan tidak ditemukan.');
        }

        return $this->employeeRepository->delete($employeeId);
    }

    /**
     * Upsert employee (for bulk import).
     */
    public function upsertEmployee(array $data): Employee
    {
        $this->validateEmployeeData($data);
        return $this->employeeRepository->upsert($data);
    }

    /**
     * Get employees for export with calculated fields.
     */
    public function getEmployeesForExport(array $filters = []): array
    {
        return $this->employeeRepository->toArray($filters);
    }

    /**
     * Import employees from array (bulk insert with upsert).
     */
    public function importEmployees(array $employeeData, bool $upsert = true): array
    {
        $results = [
            'total' => count($employeeData),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($employeeData as $index => $row) {
            try {
                if ($upsert) {
                    $this->upsertEmployee($row);
                } else {
                    $this->createEmployee($row);
                }
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 1,
                    'data' => $row,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_employees' => $this->employeeRepository->count(),
            'by_department' => $this->employeeRepository->getCountByDepartment(),
            'by_status' => $this->employeeRepository->getCountByStatusPKWTT(),
            'departments' => $this->employeeRepository->getDepartments(),
        ];
    }

    /**
     * Validate employee data (sesuai skema 26 Poin).
     */
    private function validateEmployeeData(array $data, bool $requireAll = true): void
    {
        $requiredFields = ['nik_karyawan', 'no_ktp', 'nama_lengkap', 'department_id', 'position_id', 'tanggal_masuk_kerja'];

        foreach ($requiredFields as $field) {
            if ($requireAll && empty($data[$field])) {
                throw new \Exception("Field '{$field}' wajib diisi.");
            }
            if (!$requireAll && isset($data[$field]) && empty($data[$field])) {
                throw new \Exception("Field '{$field}' tidak boleh kosong.");
            }
        }

        // Validasi format tanggal
        foreach (['tanggal_masuk_kerja', 'tanggal_lahir'] as $dateField) {
            if (!empty($data[$dateField]) && !\strtotime($data[$dateField])) {
                throw new \Exception("Format tanggal tidak valid untuk '{$dateField}'. Gunakan YYYY-MM-DD.");
            }
        }

        // Validasi Jenis Kelamin
        if (!empty($data['jenis_kelamin']) && !in_array($data['jenis_kelamin'], ['L', 'P'])) {
            throw new \Exception("Jenis kelamin tidak valid. Harus 'L' atau 'P'.");
        }

        // Validasi Status PKWTT
        if (!empty($data['status_pkwtt']) && !in_array(strtoupper($data['status_pkwtt']), ['TETAP', 'KONTRAK', 'HARIAN', 'MAGANG'])) {
            throw new \Exception("Status PKWTT tidak valid. Harus 'TETAP', 'KONTRAK', 'HARIAN', atau 'MAGANG'.");
        }

        // Validasi Status Keluarga
        if (!empty($data['status_keluarga']) && !in_array(ucwords($data['status_keluarga']), ['Lajang', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])) {
            throw new \Exception("Status keluarga tidak valid. Harus 'Lajang', 'Kawin', 'Cerai Hidup', atau 'Cerai Mati'.");
        }
    }
}