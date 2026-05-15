<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use App\Exports\EmployeeTemplateExport;
use App\Exports\EmployeeExport;
use App\Imports\EmployeeImport;
use App\Jobs\ImportEmployeesJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeImportExportController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Export employees to Excel.
     * Only HR can perform this action.
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['department', 'status_pkwtt']);

            $employees = $this->employeeService->getEmployeesForExport($filters);

            // Export to Excel using streaming
            return Excel::download(
                new EmployeeExport($employees),
                'employees_' . now()->format('Y-m-d_His') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Import employees from Excel (Async Processing via Queue).
     * File diproses di background untuk menghindari timeout pada request besar.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
            ]);

            $file = $request->file('file');

            // Parse Excel file
            $import = new EmployeeImport();
            Excel::import($import, $file);

            // Get the imported data
            $employeeData = $import->getEmployees();

            if (empty($employeeData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak mengandung data atau format tidak valid',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Dispatch ke queue untuk async processing
            $job = new ImportEmployeesJob(
                $employeeData,
                auth()->id(),
                $file->getClientOriginalName()
            );

            $jobId = \Illuminate\Support\Str::uuid();

            // Dispatch ke queue dengan job ID untuk tracking
            \Illuminate\Support\Facades\Bus::batch([
                $job,
            ])->dispatch();

            Log::info('Import job dispatched to queue', [
                'user_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'total_records' => count($employeeData),
                'job_id' => $jobId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diunggah. Import sedang diproses di background. Silahkan cek log untuk progress.',
                'data' => [
                    'total_records' => count($employeeData),
                    'file_name' => $file->getClientOriginalName(),
                    'timestamp' => now(),
                    'note' => 'Proses import dilakukan secara asynchronous. Hasil akan tersimpan di activity log.',
                ],
            ], Response::HTTP_ACCEPTED);

        } catch (\Exception $e) {
            Log::error('Employee import failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get import template.
     */
    public function getTemplate()
    {
        try {
            $template = [
                [
                    'nik' => 'NIK',
                    'no_ktp' => 'No. KTP',
                    'nama' => 'Nama',
                    'department' => 'Department',
                    'jabatan' => 'Jabatan',
                    'tempat_lahir' => 'Tempat Lahir',
                    'tanggal_lahir' => 'Tanggal Lahir (YYYY-MM-DD)',
                    'tanggal_masuk' => 'Tanggal Masuk (YYYY-MM-DD)',
                    'jenis_kelamin' => 'Jenis Kelamin (L/P)',
                    'dept_on_line' => 'Dept On Line',
                    'dept_on_line_awal' => 'Dept On Line Awal',
                    'status_pkwtt' => 'Status PKWTT (TETAP/KONTRAK)',
                    'status_keluarga' => 'Status Keluarga',
                    'pendidikan' => 'Pendidikan',
                    'alamat' => 'Alamat',
                ],
            ];

            return Excel::download(
                new EmployeeTemplateExport($template),
                'employee_import_template_' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
