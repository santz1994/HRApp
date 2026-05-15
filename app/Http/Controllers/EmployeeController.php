<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Get list of employees with filters and pagination.
     * SECURITY FIX: DoS protection + proper error handling
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'department', 'status_pkwtt', 'jenis_kelamin']);
            $sortBy = $request->input('sort_by', 'nama');
            $sortDir = $request->input('sort_dir', 'asc');

            // SECURITY FIX: Prevent DoS by limiting per_page maximum to 100
            $perPage = min((int) $request->input('per_page', 20), 100);

            $employees = $this->employeeService->getEmployeesList(
                $filters,
                $sortBy,
                $sortDir,
                $perPage
            );

            return response()->json([
                'success' => true,
                'data' => $employees->items(),
                'total' => $employees->total(),
                'per_page' => $employees->perPage(),
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
            ]);
        } catch (\Exception $e) {
            // SECURITY FIX: Log error details, don't expose to frontend
            Log::error('Employee fetch failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employees. Please contact support.',
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
     * SECURITY FIX: Use FormRequest with built-in authorization
     */
    public function store(StoreEmployeeRequest $request)
    {
        try {
            // Validation and authorization already checked by FormRequest
            $employee = $this->employeeService->createEmployee($request->validated());

            Log::info('Employee created', [
                'employee_id' => $employee->id,
                'created_by' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Employee creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee. Please contact support.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update employee data.
     * SECURITY FIX: Use FormRequest with authorization
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $updated = $this->employeeService->updateEmployee($id, $request->validated());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update employee',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Log::info('Employee updated', [
                'employee_id' => $id,
                'updated_by' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
                'data' => $this->employeeService->getEmployeeDetails($id),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Employee update failed', [
                'employee_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee. Please contact support.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printIdCard($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            
            // Log aktivitas
            AuditLogService::log('EXPORT', "Mencetak ID Card untuk NIK: {$employee->nik}", $employee);

            // Generate PDF dengan paper size A8 landscape (kartu identitas standar)
            $pdf = Pdf::loadView('pdf.id-card', compact('employee'))
                ->setPaper('a8', 'landscape')
                ->setOption('margin-top', 0)
                ->setOption('margin-right', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0);
            
            return $pdf->stream("ID_Card_{$employee->nik}.pdf");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('ID Card generation failed', [
                'employee_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate ID Card. Please contact support.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete employee.
     * SECURITY FIX: Defense in depth - check authorization at method level
     */
    public function destroy($id)
    {
        try {
            // Defense in depth: Verify authorization
            if (auth()->user()->role !== 'HR') {
                Log::warning('Unauthorized deletion attempt', [
                    'employee_id' => $id,
                    'user_id' => auth()->id(),
                    'role' => auth()->user()->role,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete employees',
                ], Response::HTTP_FORBIDDEN);
            }

            $employee = Employee::findOrFail($id);
            $deleted = $this->employeeService->deleteEmployee($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete employee',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Log::info('Employee deleted', [
                'employee_id' => $id,
                'employee_name' => $employee->nama,
                'deleted_by' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Employee deletion failed', [
                'employee_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee. Please contact support.',
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
