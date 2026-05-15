<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use App\Services\AuditLogService;
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
     * Export employees to Excel (dengan Chunking sesuai Project.md).
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['department', 'status_pkwtt']);

            $employees = $this->employeeService->getEmployeesForExport($filters);

            AuditLogService::log('EXPORT', "Export data karyawan (" . count($employees) . " baris)");

            return Excel::download(
                new EmployeeExport($employees),
                'data_karyawan_' . now()->format('Y-m-d_His') . '.xlsx'
            );
        } catch (\Exception $e) {
            Log::error('Export karyawan gagal', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal export: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Import employees from Excel (Async Processing via Queue).
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            ]);

            $file = $request->file('file');

            // Parse Excel file
            $import = new EmployeeImport();
            Excel::import($import, $file);

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

            \Illuminate\Support\Facades\Bus::batch([
                $job,
            ])->dispatch();

            AuditLogService::log('IMPORT', "Import data karyawan: {$file->getClientOriginalName()} (" . count($employeeData) . " baris)");

            Log::info('Import job dispatched ke queue', [
                'user_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'total_records' => count($employeeData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diunggah. Import sedang diproses di background.',
                'data' => [
                    'total_records' => count($employeeData),
                    'file_name' => $file->getClientOriginalName(),
                    'timestamp' => now(),
                ],
            ], Response::HTTP_ACCEPTED);

        } catch (\Exception $e) {
            Log::error('Import karyawan gagal', [
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
                    'nik_karyawan' => 'NIK Karyawan',
                    'no_ktp' => 'No. KTP',
                    'nama_lengkap' => 'Nama Lengkap',
                    'department_id' => 'Department ID',
                    'position_id' => 'Position ID',
                    'tempat_lahir' => 'Tempat Lahir',
                    'tanggal_lahir' => 'Tanggal Lahir (YYYY-MM-DD)',
                    'tanggal_masuk_kerja' => 'Tanggal Masuk Kerja (YYYY-MM-DD)',
                    'jenis_kelamin' => 'Jenis Kelamin (L/P)',
                    'status_pkwtt' => 'Status PKWTT (TETAP/KONTRAK/HARIAN/MAGANG)',
                    'status_keluarga' => 'Status Keluarga (Lajang/Kawin)',
                    'jumlah_anak' => 'Jumlah Anak',
                    'pendidikan' => 'Pendidikan',
                    'alamat_ktp' => 'Alamat KTP',
                    'alamat_domisili' => 'Alamat Domisili',
                ],
            ];

            return Excel::download(
                new EmployeeTemplateExport($template),
                'template_import_karyawan_' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}