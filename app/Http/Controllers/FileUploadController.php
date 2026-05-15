<?php

namespace App\Http\Controllers;

use App\Jobs\ImportEmployeesJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;

class FileUploadController extends Controller
{
    /**
     * Handle file upload untuk import data karyawan
     * POST /api/upload-employees
     */
    public function uploadEmployees(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            ]);

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();

            Log::info('Employee file upload started', [
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'user_id' => auth()->id(),
            ]);

            // Attempt to parse the Excel file
            try {
                $import = new EmployeeImport();
                Excel::import($import, $file);
                $data = $import->getEmployees();

                if (empty($data)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File tidak mengandung data atau format tidak valid',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                Log::info('Excel file parsed successfully', [
                    'total_rows' => count($data),
                    'file_name' => $fileName,
                ]);

                // Dispatch ke queue untuk async processing
                ImportEmployeesJob::dispatch($data, auth()->id(), $fileName)
                    ->onQueue('default');

                return response()->json([
                    'success' => true,
                    'message' => 'File berhasil diunggah dan diproses di background',
                    'data' => [
                        'file_name' => $fileName,
                        'total_records' => count($data),
                        'status' => 'processing',
                        'timestamp' => now(),
                    ],
                ], Response::HTTP_ACCEPTED);

            } catch (\Exception $e) {
                Log::error('Excel parsing error', [
                    'file_name' => $fileName,
                    'error' => $e->getMessage(),
                ]);

                // Fallback: Simpan file untuk manual processing
                $path = $file->store('imports/uploads');

                return response()->json([
                    'success' => true,
                    'message' => 'File berhasil disimpan untuk pemrosesan manual',
                    'data' => [
                        'file_path' => $path,
                        'file_name' => $fileName,
                        'timestamp' => now(),
                    ],
                ], Response::HTTP_ACCEPTED);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('File upload error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat upload file',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check import status
     * GET /api/import-status
     */
    public function importStatus()
    {
        $recent = \App\Models\ActivityLog::where('user_id', auth()->id())
            ->where('action', 'IMPORT')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $recent ? [
                'action' => $recent->action,
                'description' => $recent->description,
                'timestamp' => $recent->created_at,
            ] : null,
        ]);
    }
}
