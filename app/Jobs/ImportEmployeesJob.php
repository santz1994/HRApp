<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Services\AuditLogService;
use App\Services\ExcelImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $employeeData;
    protected $userId;
    protected $fileName;

    /**
     * Inisialisasi job untuk import karyawan dari Excel
     *
     * @param array $employeeData Data karyawan yang di-parse dari file Excel
     * @param int $userId ID user yang melakukan import
     * @param string $fileName Nama file yang di-import
     */
    public function __construct(array $employeeData, int $userId, string $fileName = 'import.xlsx')
    {
        $this->employeeData = $employeeData;
        $this->userId = $userId;
        $this->fileName = $fileName;

        // Set queue ke 'default' atau bisa dikonfigurasi di env
        $this->queue = env('QUEUE_NAME', 'default');

        // Timeout untuk proses import (dalam detik) - 300 detik = 5 menit untuk 1000+ rows
        $this->timeout = 300;

        // Retry jika ada error - max 3 kali
        $this->tries = 3;

        // Delay sebelum retry (dalam detik)
        $this->backoff = [10, 30, 60];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info('Starting employee import job', [
                'user_id' => $this->userId,
                'file_name' => $this->fileName,
                'total_records' => count($this->employeeData),
            ]);

            // Proses import dalam chunks untuk efisiensi memori
            $excelImportService = new ExcelImportService(
                app('App\Repositories\EmployeeRepository')
            );

            // Pisahkan data menjadi chunks (1000 records per chunk)
            $chunks = array_chunk($this->employeeData, 1000);
            $totalSuccess = 0;
            $totalFailed = 0;
            $allErrors = [];

            foreach ($chunks as $chunkIndex => $chunk) {
                try {
                    $results = $excelImportService->importFromArray($chunk);

                    $totalSuccess += $results['success'];
                    $totalFailed += $results['failed'];
                    $allErrors = array_merge($allErrors, $results['errors']);

                    Log::info('Chunk processed', [
                        'chunk' => $chunkIndex + 1,
                        'total_chunks' => count($chunks),
                        'success' => $results['success'],
                        'failed' => $results['failed'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error processing chunk', [
                        'chunk' => $chunkIndex + 1,
                        'error' => $e->getMessage(),
                    ]);
                    
                    throw $e;
                }
            }

            // Log aktivitas ke audit trail
            AuditLogService::log(
                'IMPORT',
                "Excel Import selesai: {$totalSuccess} berhasil, {$totalFailed} gagal dari {$this->fileName}",
                null
            );

            Log::info('Employee import job completed successfully', [
                'user_id' => $this->userId,
                'total_success' => $totalSuccess,
                'total_failed' => $totalFailed,
                'total_errors' => count($allErrors),
            ]);

            // Simpan summary ke file atau kirim notifikasi ke user
            $this->saveSummary($totalSuccess, $totalFailed, $allErrors);

        } catch (\Exception $e) {
            Log::error('Employee import job failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            AuditLogService::log(
                'IMPORT_FAILED',
                "Excel Import gagal: {$e->getMessage()}",
                null
            );

            // Re-throw untuk trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Employee import job permanently failed', [
            'user_id' => $this->userId,
            'file_name' => $this->fileName,
            'error' => $exception->getMessage(),
        ]);

        AuditLogService::log(
            'IMPORT_PERMANENT_FAILURE',
            "Excel Import gagal permanen setelah retries: {$exception->getMessage()}",
            null
        );
    }

    /**
     * Simpan summary hasil import
     */
    private function saveSummary(int $success, int $failed, array $errors): void
    {
        $summary = [
            'file_name' => $this->fileName,
            'user_id' => $this->userId,
            'timestamp' => now(),
            'total_success' => $success,
            'total_failed' => $failed,
            'errors' => $errors,
        ];

        // Bisa disimpan ke database, file, atau cache untuk reference user
        // Di sini kita simpan ke storage/logs
        \Illuminate\Support\Facades\Storage::disk('local')->put(
            "import_logs/summary_{$this->userId}_" . now()->format('Y-m-d_His') . '.json',
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
