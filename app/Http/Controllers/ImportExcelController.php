<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Services\AuditLogService;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import Excel Controller - Khusus untuk Upload & Import Karyawan
 * Menggunakan Bahasa Indonesia sesuai Project.md
 */
class ImportExcelController extends Controller
{
    protected $excelImportService;
    protected $auditLogService;

    public function __construct(ExcelImportService $excelImportService, AuditLogService $auditLogService)
    {
        $this->excelImportService = $excelImportService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Upload dan preview data Excel
     * Endpoint: POST /api/import/preview
     */
    public function previewUpload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            ], [
                'file.required' => 'File wajib diunggah',
                'file.mimes' => 'File harus format Excel (.xlsx, .xls) atau CSV',
                'file.max' => 'File tidak boleh lebih dari 10 MB',
            ]);

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            if (empty($data) || (count($data) <= 1)) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'File kosong atau tidak mengandung data',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Ambil header (baris pertama)
            $header = array_shift($data);

            // Ambil 5 baris data pertama untuk preview
            $preview = array_slice($data, 0, 5);

            // Transformasi ke format yang readable
            $previewData = [];
            foreach ($preview as $rowIdx => $row) {
                $formattedRow = [];
                foreach ($header as $colIdx => $headerName) {
                    $formattedRow[$headerName] = $row[$colIdx] ?? null;
                }
                $previewData[] = $formattedRow;
            }

            return response()->json([
                'sukses' => true,
                'pesan' => 'File berhasil dibaca',
                'data' => [
                    'nama_file' => $file->getClientOriginalName(),
                    'ukuran_file' => $file->getSize() . ' bytes',
                    'total_baris_data' => count($data),
                    'header_kolom' => $header,
                    'preview_data' => $previewData,
                    'timestamp_upload' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Preview upload gagal', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal membaca file: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Import data Excel ke database
     * Endpoint: POST /api/import/process
     */
    public function processImport(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            ], [
                'file.required' => 'File wajib diunggah',
                'file.mimes' => 'File harus format Excel (.xlsx, .xls) atau CSV',
                'file.max' => 'File tidak boleh lebih dari 10 MB',
            ]);

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            if (empty($data) || count($data) <= 1) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'File kosong atau tidak mengandung data',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Ambil header
            $header = array_shift($data);

            // Transformasi ke array dengan key dari header
            $mappedData = [];
            foreach ($data as $row) {
                $mappedRow = [];
                foreach ($header as $colIdx => $headerName) {
                    $mappedRow[$headerName] = $row[$colIdx] ?? null;
                }
                if (!empty(array_filter($mappedRow))) { // Skip baris kosong
                    $mappedData[] = $mappedRow;
                }
            }

            if (empty($mappedData)) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Tidak ada data valid untuk diimpor',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Import menggunakan ExcelImportService
            $results = $this->excelImportService->importFromArray($mappedData);

            // Log audit
            $this->auditLogService->log(
                auth()->id(),
                'IMPORT_KARYAWAN',
                'employees',
                ['file' => $file->getClientOriginalName()],
                $results
            );

            Log::info('Import karyawan berhasil', [
                'user_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'total_imported' => $results['success'],
                'total_failed' => $results['failed'],
            ]);

            return response()->json([
                'sukses' => true,
                'pesan' => 'Import berhasil selesai',
                'data' => [
                    'nama_file' => $file->getClientOriginalName(),
                    'total_baris' => count($mappedData),
                    'berhasil' => $results['success'],
                    'gagal' => $results['failed'],
                    'id_karyawan_diimpor' => $results['imported_ids'],
                    'error_detail' => $results['errors'],
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Import karyawan gagal', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal mengimpor file: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Download template Excel
     * Endpoint: GET /api/import/template
     */
    public function downloadTemplate()
    {
        try {
            $departments = Department::select('id', 'nama')->get();
            $positions = Position::select('id', 'nama')->get();

            $templateData = [
                [
                    'nik_karyawan' => 'Contoh: 1001',
                    'no_ktp' => 'Contoh: 3171234567890123',
                    'nama_lengkap' => 'Contoh: Budi Santoso',
                    'nama_departemen' => 'Contoh: ' . ($departments->first()?->nama ?? 'IT'),
                    'nama_jabatan' => 'Contoh: ' . ($positions->first()?->nama ?? 'Developer'),
                    'tempat_lahir' => 'Contoh: Jakarta',
                    'tanggal_lahir' => 'Format: YYYY-MM-DD',
                    'tanggal_masuk_kerja' => 'Format: YYYY-MM-DD',
                    'jenis_kelamin' => 'Contoh: L atau P',
                    'status_pkwtt' => 'Contoh: TETAP atau KONTRAK',
                    'status_keluarga' => 'Contoh: Lajang atau Kawin',
                    'jumlah_anak' => 'Contoh: 2',
                    'pendidikan' => 'Contoh: S1',
                    'alamat_ktp' => 'Alamat sesuai KTP',
                    'alamat_domisili' => 'Alamat domisili saat ini',
                ],
            ];

            // Return as JSON untuk sekarang (atau bisa generate Excel)
            return response()->json([
                'sukses' => true,
                'pesan' => 'Template siap diunduh',
                'data' => [
                    'template' => $templateData,
                    'daftar_departemen' => $departments->pluck('nama'),
                    'daftar_jabatan' => $positions->pluck('nama'),
                    'catatan' => [
                        'Kolom nik_karyawan dan nama_lengkap WAJIB diisi',
                        'Format tanggal: YYYY-MM-DD (contoh: 2020-01-15)',
                        'Status PKWTT: TETAP, KONTRAK, HARIAN, atau MAGANG',
                        'Jenis kelamin: L (Laki-laki) atau P (Perempuan)',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Download template gagal', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal membuat template',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Status import terakhir
     * Endpoint: GET /api/import/status
     */
    public function importStatus()
    {
        try {
            $stats = [
                'total_karyawan' => Employee::count(),
                'karyawan_aktif' => Employee::whereNull('deleted_at')->count(),
                'karyawan_terhapus' => Employee::onlyTrashed()->count(),
                'import_terakhir' => DB::table('activity_logs')
                    ->where('action', 'IMPORT_KARYAWAN')
                    ->where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->first(),
            ];

            return response()->json([
                'sukses' => true,
                'pesan' => 'Status import',
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal mengambil status import',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validasi data tanpa import
     * Endpoint: POST /api/import/validate
     */
    public function validateData(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            ], [
                'file.required' => 'File wajib diunggah',
                'file.mimes' => 'File harus format Excel (.xlsx, .xls) atau CSV',
                'file.max' => 'File tidak boleh lebih dari 10 MB',
            ]);

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            if (empty($data) || count($data) <= 1) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'File kosong',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $header = array_shift($data);
            $mappedData = [];
            $errors = [];

            foreach ($data as $rowIdx => $row) {
                $mappedRow = [];
                foreach ($header as $colIdx => $headerName) {
                    $mappedRow[$headerName] = $row[$colIdx] ?? null;
                }
                if (!empty(array_filter($mappedRow))) {
                    $mappedData[] = ['row' => $rowIdx + 2, 'data' => $mappedRow];
                }
            }

            // Validasi setiap baris
            foreach ($mappedData as $idx => $item) {
                $data = $item['data'];
                if (empty($data['nik_karyawan'])) {
                    $errors[] = [
                        'baris' => $item['row'],
                        'error' => 'NIK Karyawan wajib diisi',
                    ];
                }
                if (empty($data['nama_lengkap'])) {
                    $errors[] = [
                        'baris' => $item['row'],
                        'error' => 'Nama Lengkap wajib diisi',
                    ];
                }
            }

            return response()->json([
                'sukses' => empty($errors),
                'pesan' => empty($errors) ? 'Data valid, siap diimpor' : 'Data tidak valid, perbaiki error di bawah',
                'data' => [
                    'total_baris' => count($mappedData),
                    'error_count' => count($errors),
                    'errors' => $errors,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal validasi: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
