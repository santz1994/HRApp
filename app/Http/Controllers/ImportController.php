<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    protected $excelImportService;

    public function __construct(ExcelImportService $excelImportService)
    {
        $this->excelImportService = $excelImportService;
    }

    /**
     * Import employees from Excel file
     * Only HR role can perform this
     */
    public function importEmployees(Request $request)
    {
        try {
            // Validate file upload
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $file = $request->file('file');
            
            // Read the Excel file
            $spreadsheet = IOFactory::load($file->path());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Extract headers from first row
            $headers = array_shift($rows);
            
            // Convert rows to associative arrays
            $data = [];
            foreach ($rows as $row) {
                if (empty(array_filter($row))) {
                    continue; // Skip empty rows
                }
                
                $item = [];
                foreach ($headers as $index => $header) {
                    $item[strtolower(str_replace([' ', '.'], '_', trim($header)))] = $row[$index] ?? null;
                }
                $data[] = $item;
            }

            // Import data
            $results = $this->excelImportService->importFromArray($data);

            return response()->json([
                'success' => true,
                'message' => "Import completed: {$results['success']} succeeded, {$results['failed']} failed",
                'data' => $results
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Export employees to Excel
     */
    public function exportEmployees()
    {
        try {
            $employees = \App\Models\Employee::all();

            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'NO',
                'NIK',
                'NO_KTP',
                'NAMA',
                'DEPT',
                'JABATAN',
                'TEMPAT_LAHIR',
                'TANGGAL_MASUK',
                'TANGGAL_LAHIR',
                'JENIS_KELAMIN',
                'DEPT_ON_LINE',
                'DEPT_ON_LINE_AWAL',
                'STATUS_PKWTT',
                'STATUS_KELUARGA',
                'PENDIDIKAN',
                'ALAMAT',
                'UMUR_MASUK',
                'MASA_KERJA'
            ];

            foreach ($headers as $col => $header) {
                $sheet->setCellValue(chr(65 + $col) . '1', $header);
            }

            // Add data rows
            $row = 2;
            foreach ($employees as $index => $emp) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $emp->nik);
                $sheet->setCellValue('C' . $row, $emp->no_ktp);
                $sheet->setCellValue('D' . $row, $emp->nama);
                $sheet->setCellValue('E' . $row, $emp->department);
                $sheet->setCellValue('F' . $row, $emp->jabatan);
                $sheet->setCellValue('G' . $row, $emp->tempat_lahir);
                $sheet->setCellValue('H' . $row, $emp->tanggal_masuk);
                $sheet->setCellValue('I' . $row, $emp->tanggal_lahir);
                $sheet->setCellValue('J' . $row, $emp->jenis_kelamin);
                $sheet->setCellValue('K' . $row, $emp->dept_on_line);
                $sheet->setCellValue('L' . $row, $emp->dept_on_line_awal);
                $sheet->setCellValue('M' . $row, $emp->status_pkwtt);
                $sheet->setCellValue('N' . $row, $emp->status_keluarga);
                $sheet->setCellValue('O' . $row, $emp->pendidikan);
                $sheet->setCellValue('P' . $row, $emp->alamat);
                $sheet->setCellValue('Q' . $row, $emp->age_on_joining);
                $sheet->setCellValue('R' . $row, $emp->tenure_formatted);
                $row++;
            }

            // Auto-fit columns
            foreach (range('A', 'R') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Generate file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'employees_' . date('Y-m-d_His') . '.xlsx';
            
            $response = response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
