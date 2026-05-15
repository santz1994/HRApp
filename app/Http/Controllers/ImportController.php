<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImportController extends Controller
{
    protected $excelImportService;

    public function __construct(ExcelImportService $excelImportService)
    {
        $this->excelImportService = $excelImportService;
    }

    /**
     * Import employees from Excel file.
     */
    public function importEmployees(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            $file = $request->file('file');

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->path());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $headers = array_shift($rows);

            $data = [];
            foreach ($rows as $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $item = [];
                foreach ($headers as $index => $header) {
                    $item[strtolower(str_replace([' ', '.'], '_', trim($header)))] = $row[$index] ?? null;
                }
                $data[] = $item;
            }

            $results = $this->excelImportService->importFromArray($data);

            return response()->json([
                'success' => true,
                'message' => "Import selesai: {$results['success']} berhasil, {$results['failed']} gagal",
                'data' => $results,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}