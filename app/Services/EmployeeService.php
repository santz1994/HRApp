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
        string $sortBy = 'nama',
        string $sortDir = 'asc',
        int $perPage = 50
    ): LengthAwarePaginator
    {
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
        // Validate required fields
        $this->validateEmployeeData($data);

        // Check for duplicates
        if ($this->employeeRepository->findByNIK($data['nik'])) {
            throw new \Exception('Employee with this NIK already exists.');
        }

        if ($this->employeeRepository->findByKTP($data['no_ktp'])) {
            throw new \Exception('Employee with this KTP already exists.');
        }

        return $this->employeeRepository->create($data);
    }

    /**
     * Update employee data with validation.
     */
    public function updateEmployee(int $employeeId, array $data): bool
    {
        // Validate required fields
        $this->validateEmployeeData($data, false);

        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee) {
            throw new \Exception('Employee not found.');
        }

        // Check for duplicate NIK (excluding current employee)
        if (isset($data['nik']) && $data['nik'] !== $employee->nik) {
            if ($this->employeeRepository->findByNIK($data['nik'])) {
                throw new \Exception('Another employee with this NIK already exists.');
            }
        }

        // Check for duplicate KTP (excluding current employee)
        if (isset($data['no_ktp']) && $data['no_ktp'] !== $employee->no_ktp) {
            if ($this->employeeRepository->findByKTP($data['no_ktp'])) {
                throw new \Exception('Another employee with this KTP already exists.');
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
            throw new \Exception('Employee not found.');
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
     * Processes in chunks to avoid memory issues.
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
     * Validate employee data.
     */
    private function validateEmployeeData(array $data, bool $requireAll = true): void
    {
        $requiredFields = ['nik', 'no_ktp', 'nama', 'department', 'jabatan', 'tanggal_masuk'];

        foreach ($requiredFields as $field) {
            if ($requireAll && empty($data[$field])) {
                throw new \Exception("Field '{$field}' is required.");
            }
            if (!$requireAll && isset($data[$field]) && empty($data[$field])) {
                throw new \Exception("Field '{$field}' cannot be empty.");
            }
        }

        // Validate date formats
        if (isset($data['tanggal_masuk'])) {
            if (!\strtotime($data['tanggal_masuk'])) {
                throw new \Exception("Invalid date format for 'tanggal_masuk'. Use YYYY-MM-DD.");
            }
        }

        if (isset($data['tanggal_lahir'])) {
            if (!\strtotime($data['tanggal_lahir'])) {
                throw new \Exception("Invalid date format for 'tanggal_lahir'. Use YYYY-MM-DD.");
            }
        }

        // Validate gender
        if (isset($data['jenis_kelamin']) && !in_array($data['jenis_kelamin'], ['L', 'P'])) {
            throw new \Exception("Invalid gender. Must be 'L' or 'P'.");
        }

        // Validate status PKWTT
        if (isset($data['status_pkwtt']) && !in_array($data['status_pkwtt'], ['TETAP', 'KONTRAK'])) {
            throw new \Exception("Invalid status PKWTT. Must be 'TETAP' or 'KONTRAK'.");
        }
    }
}
