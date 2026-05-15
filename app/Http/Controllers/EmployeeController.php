<?php

namespace App\Http\Controllers;

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
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'department', 'status_pkwtt', 'jenis_kelamin']);
            $sortBy = $request->input('sort_by', 'nama_lengkap');
            $sortDir = $request->input('sort_dir', 'asc');
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
            Log::error('Gagal mengambil data karyawan', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data karyawan. Hubungi admin.',
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
                    'message' => 'Karyawan tidak ditemukan',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $employee->toArray();

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
     */
    public function store(StoreEmployeeRequest $request)
    {
        try {
            $employee = $this->employeeService->createEmployee($request->validated());

            Log::info('Karyawan berhasil dibuat', [
                'employee_id' => $employee->id,
                'created_by' => auth()->user()->email,
            ]);

            AuditLogService::log('CREATE', "Menambahkan karyawan baru: {$employee->nama_lengkap}", $employee);

            return response()->json([
                'success' => true,
                'message' => 'K berhasil ditambahkan',
                'data' => $employee,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Gagal membuat karyawan', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan karyawan: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update employee data.
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {
            $updated = $this->employeeService->updateEmployee($id, $request->validated());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data karyawan',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $employee = $this->employeeService->getEmployeeDetails($id);

            Log::info('Data karyawan diperbarui', [
                'employee_id' => $id,
                'updated_by' => auth()->user()->email,
            ]);

            AuditLogService::log('UPDATE', "Memperbarui data karyawan: {$employee->nama_lengkap}", $employee);

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diperbarui',
                'data' => $employee,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui karyawan', [
                'employee_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui karyawan: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Print ID Card.
     */
    public function printIdCard($id)
    {
        try {
            $employee = Employee::with(['department', 'position'])->findOrFail($id);

            AuditLogService::log('EXPORT', "Mencetak ID Card untuk NIK: {$employee->nik_karyawan}", $employee);

            $pdf = Pdf::loadView('pdf.id-card', compact('employee'))
                ->setPaper('a8', 'landscape')
                ->setOption('margin-top', 0)
                ->setOption('margin-right', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0);

            return $pdf->stream("ID_Card_{$employee->nik_karyawan}.pdf");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Gagal mencetak ID Card', [
                'employee_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak ID Card: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete employee (hanya HR yang boleh).
     */
    public function destroy($id)
    {
        try {
            // PERBAIKAN: Gunakan hasRole() untuk cek role, bukan membandingkan object
            if (!auth()->user()->hasRole('hr')) {
                Log::warning('Percobaan hapus karyawan tanpa otorisasi', [
                    'employee_id' => $id,
                    'user_id' => auth()->id(),
                    'role' => auth()->user()->role?->slug,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus karyawan',
                ], Response::HTTP_FORBIDDEN);
            }

            $employee = Employee::findOrFail($id);
            $deleted = $this->employeeService->deleteEmployee($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus karyawan',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Log::info('Karyawan dihapus', [
                'employee_id' => $id,
                'employee_name' => $employee->nama_lengkap,
                'deleted_by' => auth()->user()->email,
            ]);

            AuditLogService::log('DELETE', "Menghapus karyawan: {$employee->nama_lengkap}", $employee);

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus karyawan', [
                'employee_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get dashboard statistics.
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