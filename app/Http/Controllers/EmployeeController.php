<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Get list of employees with filters and pagination.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'department', 'status_pkwtt', 'jenis_kelamin']);
            $sortBy = $request->input('sort_by', 'nama');
            $sortDir = $request->input('sort_dir', 'asc');
            $perPage = $request->input('per_page', 50);

            $employees = $this->employeeService->getEmployeesList(
                $filters,
                $sortBy,
                $sortDir,
                (int) $perPage
            );

            return response()->json([
                'success' => true,
                'data' => $employees->items(),
                'meta' => [
                    'total' => $employees->total(),
                    'per_page' => $employees->perPage(),
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get employee details.
     */
    public function show($id)
    {
        try {
            $employee = $this->employeeService->getEmployeeDetails($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                ], Response::HTTP_NOT_FOUND);
            }

            // Add calculated fields
            $data = $employee->toArray();
            $data['age'] = $employee->age;
            $data['age_on_joining'] = $employee->age_on_joining;
            $data['tenure_years'] = $employee->tenure_years;
            $data['tenure_formatted'] = $employee->tenure_formatted;

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new employee.
     * Only HR can perform this action.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nik' => 'required|string|unique:employees',
                'no_ktp' => 'required|string|unique:employees',
                'nama' => 'required|string',
                'department' => 'required|string',
                'jabatan' => 'required|string',
                'tempat_lahir' => 'nullable|string',
                'tanggal_lahir' => 'nullable|date',
                'tanggal_masuk' => 'required|date',
                'jenis_kelamin' => 'nullable|in:L,P',
                'dept_on_line' => 'nullable|string',
                'dept_on_line_awal' => 'nullable|string',
                'status_pkwtt' => 'required|in:TETAP,KONTRAK',
                'status_keluarga' => 'nullable|string',
                'pendidikan' => 'nullable|string',
                'alamat' => 'nullable|string',
            ]);

            $employee = $this->employeeService->createEmployee($validated);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update employee data.
     * Only HR can perform this action.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nik' => 'sometimes|string|unique:employees,nik,' . $id,
                'no_ktp' => 'sometimes|string|unique:employees,no_ktp,' . $id,
                'nama' => 'sometimes|string',
                'department' => 'sometimes|string',
                'jabatan' => 'sometimes|string',
                'tempat_lahir' => 'nullable|string',
                'tanggal_lahir' => 'nullable|date',
                'tanggal_masuk' => 'sometimes|date',
                'jenis_kelamin' => 'nullable|in:L,P',
                'dept_on_line' => 'nullable|string',
                'dept_on_line_awal' => 'nullable|string',
                'status_pkwtt' => 'sometimes|in:TETAP,KONTRAK',
                'status_keluarga' => 'nullable|string',
                'pendidikan' => 'nullable|string',
                'alamat' => 'nullable|string',
            ]);

            $updated = $this->employeeService->updateEmployee($id, $validated);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update employee',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
                'data' => $this->employeeService->getEmployeeDetails($id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete employee.
     * Only HR can perform this action.
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->employeeService->deleteEmployee($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete employee',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get dashboard statistics.
     * Accessible by both HR and Director.
     */
    public function statistics()
    {
        try {
            $stats = $this->employeeService->getDashboardStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
