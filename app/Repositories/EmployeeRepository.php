<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeRepository
{
    protected $model;

    public function __construct(Employee $model)
    {
        $this->model = $model;
    }

    /**
     * Get all employees with pagination and filters.
     * 
     * @param array $filters ['search', 'department', 'status_pkwtt', 'jenis_kelamin']
     * @param string $sortBy Column name to sort by
     * @param string $sortDir Sort direction (asc or desc)
     * @param int $perPage Items per page
     */
    public function getEmployees(
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 50
    ): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply department filter
        if (!empty($filters['department'])) {
            $query->byDepartment($filters['department']);
        }

        // Apply status PKWTT filter
        if (!empty($filters['status_pkwtt'])) {
            $query->byStatusPKWTT($filters['status_pkwtt']);
        }

        // Apply gender filter
        if (!empty($filters['jenis_kelamin'])) {
            $query->byGender($filters['jenis_kelamin']);
        }

        // Apply sorting
        $query->orderBy($sortBy, strtolower($sortDir) === 'asc' ? 'asc' : 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get employee by ID.
     */
    public function findById(int $id): ?Employee
    {
        return $this->model->find($id);
    }

    /**
     * Get employee by NIK.
     */
    public function findByNIK(string $nik): ?Employee
    {
        return $this->model->where('nik', $nik)->first();
    }

    /**
     * Get employee by KTP.
     */
    public function findByKTP(string $noKtp): ?Employee
    {
        return $this->model->where('no_ktp', $noKtp)->first();
    }

    /**
     * Create a new employee.
     */
    public function create(array $data): Employee
    {
        return $this->model->create($data);
    }

    /**
     * Update an employee.
     */
    public function update(int $id, array $data): bool
    {
        $employee = $this->findById($id);
        if (!$employee) {
            return false;
        }
        return $employee->update($data);
    }

    /**
     * Delete an employee (soft delete).
     */
    public function delete(int $id): bool
    {
        $employee = $this->findById($id);
        if (!$employee) {
            return false;
        }
        return $employee->delete();
    }

    /**
     * Get employees by department.
     */
    public function getByDepartment(string $department, int $perPage = 50)
    {
        return $this->model->byDepartment($department)
            ->orderBy('nama')
            ->paginate($perPage);
    }

    /**
     * Get employees by status PKWTT.
     */
    public function getByStatusPKWTT(string $status, int $perPage = 50)
    {
        return $this->model->byStatusPKWTT($status)
            ->orderBy('nama')
            ->paginate($perPage);
    }

    /**
     * Get all unique departments.
     */
    public function getDepartments()
    {
        return $this->model->distinct()
            ->pluck('department')
            ->sort()
            ->values();
    }

    /**
     * Get employee count by department.
     */
    public function getCountByDepartment()
    {
        return $this->model->groupBy('department')
            ->selectRaw('department, COUNT(*) as count')
            ->get()
            ->pluck('count', 'department');
    }

    /**
     * Get employee count by status PKWTT.
     */
    public function getCountByStatusPKWTT()
    {
        return $this->model->groupBy('status_pkwtt')
            ->selectRaw('status_pkwtt, COUNT(*) as count')
            ->get()
            ->pluck('count', 'status_pkwtt');
    }

    /**
     * Upsert employee (update if exists, create if not).
     */
    public function upsert(array $data): Employee
    {
        // Check if employee exists by NIK or KTP
        $employee = $this->findByNIK($data['nik']) ?? $this->findByKTP($data['no_ktp'] ?? null);

        if ($employee) {
            $employee->update($data);
            return $employee;
        }

        return $this->create($data);
    }

    /**
     * Chunk query results for memory-efficient processing.
     */
    public function chunk(int $size, callable $callback, array $filters = []): void
    {
        $query = $this->model->newQuery();

        // Apply filters if provided
        if (!empty($filters['department'])) {
            $query->byDepartment($filters['department']);
        }
        if (!empty($filters['status_pkwtt'])) {
            $query->byStatusPKWTT($filters['status_pkwtt']);
        }

        $query->chunk($size, $callback);
    }

    /**
     * Get total count of employees.
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Export employees to array with calculated fields.
     */
    public function toArray(array $filters = []): array
    {
        $query = $this->model->newQuery();

        if (!empty($filters['department'])) {
            $query->byDepartment($filters['department']);
        }
        if (!empty($filters['status_pkwtt'])) {
            $query->byStatusPKWTT($filters['status_pkwtt']);
        }

        return $query->get()
            ->map(function (Employee $employee) {
                return [
                    'nik' => $employee->nik,
                    'no_ktp' => $employee->no_ktp,
                    'nama' => $employee->nama,
                    'department' => $employee->department,
                    'jabatan' => $employee->jabatan,
                    'tempat_lahir' => $employee->tempat_lahir,
                    'tanggal_lahir' => $employee->tanggal_lahir?->format('Y-m-d'),
                    'tanggal_masuk' => $employee->tanggal_masuk?->format('Y-m-d'),
                    'jenis_kelamin' => $employee->jenis_kelamin,
                    'umur_sekarang' => $employee->age,
                    'umur_saat_masuk' => $employee->age_on_joining,
                    'masa_kerja' => $employee->tenure_formatted,
                    'status_pkwtt' => $employee->status_pkwtt,
                    'status_keluarga' => $employee->status_keluarga,
                    'pendidikan' => $employee->pendidikan,
                    'alamat_ktp' => $employee->alamat_ktp,
                    'alamat_domisili' => $employee->alamat_domisili,
                ];
            })
            ->toArray();
    }
}
