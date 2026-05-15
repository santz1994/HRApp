<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Department;
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
     */
    public function getEmployees(
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 50
    ): LengthAwarePaginator {
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
        $allowedSorts = [
            'nik_karyawan', 'nama_lengkap', 'tanggal_lahir', 'tanggal_masuk_kerja',
            'jenis_kelamin', 'status_pkwtt', 'created_at',
        ];

        $sortColumn = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $query->orderBy($sortColumn, strtolower($sortDir) === 'asc' ? 'asc' : 'desc');

        // Eager load relasi
        $query->with(['department', 'position']);

        return $query->paginate($perPage);
    }

    /**
     * Get employee by ID with relations.
     */
    public function findById(int $id): ?Employee
    {
        return $this->model->with(['department', 'position', 'initialDepartment', 'currentDepartment'])->find($id);
    }

    /**
     * Get employee by NIK (nik_karyawan).
     */
    public function findByNIK(string $nik): ?Employee
    {
        return $this->model->where('nik_karyawan', $nik)->first();
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
    public function getByDepartment(int $departmentId, int $perPage = 50)
    {
        return $this->model->byDepartment($departmentId)
            ->orderBy('nama_lengkap')
            ->paginate($perPage);
    }

    /**
     * Get employees by status PKWTT.
     */
    public function getByStatusPKWTT(string $status, int $perPage = 50)
    {
        return $this->model->byStatusPKWTT($status)
            ->orderBy('nama_lengkap')
            ->paginate($perPage);
    }

    /**
     * Get all departments from departments table.
     */
    public function getDepartments()
    {
        return Department::orderBy('name')->get();
    }

    /**
     * Get employee count by department (using FK).
     */
    public function getCountByDepartment()
    {
        return $this->model->join('departments', 'employees.department_id', '=', 'departments.id')
            ->groupBy('departments.name')
            ->selectRaw('departments.name as department_name, COUNT(*) as count')
            ->pluck('count', 'department_name');
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
     * Upsert employee (update if exists by NIK or KTP, create if not).
     */
    public function upsert(array $data): Employee
    {
        $employee = null;

        if (!empty($data['nik_karyawan'])) {
            $employee = $this->findByNIK($data['nik_karyawan']);
        }

        if (!$employee && !empty($data['no_ktp'])) {
            $employee = $this->findByKTP($data['no_ktp']);
        }

        if ($employee) {
            $employee->update($data);
            return $employee->fresh();
        }

        return $this->create($data);
    }

    /**
     * Chunk query results for memory-efficient processing.
     */
    public function chunk(int $size, callable $callback, array $filters = []): void
    {
        $query = $this->model->newQuery();

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
     * Export employees to array with calculated fields (chunked for memory efficiency).
     */
    public function toArray(array $filters = []): array
    {
        $query = $this->model->with(['department', 'position']);

        if (!empty($filters['department'])) {
            $query->byDepartment($filters['department']);
        }
        if (!empty($filters['status_pkwtt'])) {
            $query->byStatusPKWTT($filters['status_pkwtt']);
        }

        $results = [];
        $query->chunk(1000, function ($chunk) use (&$results) {
            foreach ($chunk as $employee) {
                $results[] = [
                    'nik_karyawan' => $employee->nik_karyawan,
                    'no_ktp' => $employee->no_ktp,
                    'nama_lengkap' => $employee->nama_lengkap,
                    'department' => $employee->department?->name ?? '',
                    'jabatan' => $employee->position?->name ?? '',
                    'tempat_lahir' => $employee->tempat_lahir,
                    'tanggal_lahir' => $employee->tanggal_lahir?->format('Y-m-d'),
                    'tanggal_masuk_kerja' => $employee->tanggal_masuk_kerja?->format('Y-m-d'),
                    'jenis_kelamin' => $employee->jenis_kelamin,
                    'usia_saat_ini' => $employee->usia_saat_ini,
                    'usia_masuk_bekerja' => $employee->usia_masuk_bekerja,
                    'masa_kerja' => $employee->masa_kerja_formatted,
                    'status_pkwtt' => $employee->status_pkwtt,
                    'status_keluarga' => $employee->status_keluarga,
                    'pendidikan' => $employee->pendidikan,
                    'alamat_ktp' => $employee->alamat_ktp,
                    'alamat_domisili' => $employee->alamat_domisili,
                ];
            }
        });

        return $results;
    }
}