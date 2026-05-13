<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     * Import employees from Excel.
     * Only HR can perform this action.
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            // Import and process file
            $import = new EmployeeImport();
            Excel::import($import, $request->file('file'));

            // Get the imported data
            $employeeData = $import->getEmployees();

            // Process import with upsert logic
            $results = $this->employeeService->importEmployees($employeeData, true);

            return response()->json([
                'success' => true,
                'message' => 'Import completed',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
