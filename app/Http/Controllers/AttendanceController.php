<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Get attendance records with filters.
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with('employee');

            // Filter by employee
            if ($request->has('employee_id')) {
                $query->byEmployee($request->input('employee_id'));
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->input('start_date'), $request->input('end_date'));
            }

            // Filter by status
            if ($request->has('status_kehadiran')) {
                $query->byStatus($request->input('status_kehadiran'));
            }

            $perPage = min((int) $request->input('per_page', 20), 100);
            $attendances = $query->orderBy('tanggal', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $attendances->items(),
                'total' => $attendances->total(),
                'per_page' => $attendances->perPage(),
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data absensi', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data absensi: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store attendance record.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'tanggal' => 'required|date',
                'jam_masuk' => 'nullable|date_format:H:i',
                'jam_pulang' => 'nullable|date_format:H:i',
                'status_kehadiran' => 'required|in:Hadir,Izin,Sakit,Alpha,Cuti',
            ]);

            $attendance = Attendance::create($request->only([
                'employee_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status_kehadiran',
            ]));

            AuditLogService::log('CREATE', "Rekam absensi untuk karyawan ID: {$request->employee_id}");

            return response()->json([
                'success' => true,
                'message' => 'Data absensi berhasil disimpan',
                'data' => $attendance,
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan absensi', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan absensi: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get attendance summary for an employee.
     */
    public function summary(Request $request, $employeeId)
    {
        try {
            $employee = Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan',
                ], Response::HTTP_NOT_FOUND);
            }

            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

            $summary = Attendance::where('employee_id', $employeeId)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->selectRaw('status_kehadiran, COUNT(*) as jumlah')
                ->groupBy('status_kehadiran')
                ->pluck('jumlah', 'status_kehadiran');

            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'nik_karyawan' => $employee->nik_karyawan,
                        'nama_lengkap' => $employee->nama_lengkap,
                    ],
                    'periode' => ['start' => $startDate, 'end' => $endDate],
                    'summary' => $summary,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}