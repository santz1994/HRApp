<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Employee;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MedicalRecordController extends Controller
{
    /**
     * Get medical records with filters.
     */
    public function index(Request $request)
    {
        try {
            $query = MedicalRecord::with('employee');

            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->input('employee_id'));
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('tanggal_mulai', [
                    $request->input('start_date'),
                    $request->input('end_date'),
                ]);
            }

            $perPage = min((int) $request->input('per_page', 20), 100);
            $records = $query->orderBy('tanggal_mulai', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $records->items(),
                'total' => $records->total(),
                'per_page' => $records->perPage(),
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data medical record', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store medical record.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'keterangan_sakit' => 'nullable|string',
                'file_skd' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            $data = $request->only(['employee_id', 'tanggal_mulai', 'tanggal_selesai', 'keterangan_sakit']);

            // Upload file SKD jika ada
            if ($request->hasFile('file_skd')) {
                $path = $request->file('file_skd')->store('medical_records', 'public');
                $data['path_file_skd'] = $path;
            }

            $record = MedicalRecord::create($data);

            AuditLogService::log('CREATE', "Rekam medical record untuk karyawan ID: {$request->employee_id}");

            return response()->json([
                'success' => true,
                'message' => 'Data medical record berhasil disimpan',
                'data' => $record,
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan medical record', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete medical record.
     */
    public function destroy($id)
    {
        try {
            $record = MedicalRecord::findOrFail($id);

            // Hapus file jika ada
            if ($record->path_file_skd && Storage::disk('public')->exists($record->path_file_skd)) {
                Storage::disk('public')->delete($record->path_file_skd);
            }

            $record->delete();

            AuditLogService::log('DELETE', "Menghapus medical record ID: {$id}");

            return response()->json([
                'success' => true,
                'message' => 'Data medical record berhasil dihapus',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}