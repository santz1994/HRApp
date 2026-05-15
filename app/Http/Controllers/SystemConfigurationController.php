<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Services\AuditLogService;

/**
 * System Configuration Controller
 * Untuk IT Developer mengatur konfigurasi sistem dan monitoring
 * Sesuai Project.md Poin D: "Konfigurasi Sistem (Khusus IT Developer)"
 */
class SystemConfigurationController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get system health status
     * Informasi database, cache, queue, storage, etc
     */
    public function getSystemHealth(Request $request)
    {
        try {
            $health = [
                'database' => $this->checkDatabaseConnection(),
                'cache' => $this->checkCacheConnection(),
                'storage' => $this->checkStorageHealth(),
                'queue' => $this->checkQueueStatus(),
                'laravel_version' => \Illuminate\Foundation\Application::VERSION,
                'php_version' => phpversion(),
                'memory_usage' => memory_get_usage() / 1024 / 1024, // MB
                'memory_limit' => ini_get('memory_limit'),
                'disk_space' => $this->getDiskSpace(),
            ];

            return response()->json([
                'success' => true,
                'data' => $health,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal check system health', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal check kesehatan sistem',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Queue Monitor (jobs, failed jobs, batches)
     * Dari Project.md: "monitoring status Queue (Supervisor)"
     */
    public function getQueueMonitor(Request $request)
    {
        try {
            $perPage = min((int) $request->input('per_page', 50), 100);

            $jobs = DB::table('jobs')->paginate($perPage);
            $failedJobs = DB::table('failed_jobs')->paginate($perPage);
            $jobBatches = DB::table('job_batches')->paginate($perPage);

            $stats = [
                'total_jobs' => DB::table('jobs')->count(),
                'total_failed_jobs' => DB::table('failed_jobs')->count(),
                'total_batches' => DB::table('job_batches')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'jobs' => $jobs->items(),
                'failed_jobs' => $failedJobs->items(),
                'batches' => $jobBatches->items(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal get queue monitor', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil monitor queue',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get configuration parameters
     * Dari Project.md: "Parameter environment dari UI"
     * Return: DB connection, cache, queue, AI endpoint, etc
     */
    public function getConfiguration(Request $request)
    {
        try {
            $config = [
                'app' => [
                    'name' => config('app.name'),
                    'env' => config('app.env'),
                    'debug' => config('app.debug'),
                    'url' => config('app.url'),
                ],
                'database' => [
                    'driver' => config('database.default'),
                    'host' => config('database.connections.' . config('database.default') . '.host'),
                    'port' => config('database.connections.' . config('database.default') . '.port'),
                    'database' => config('database.connections.' . config('database.default') . '.database'),
                ],
                'cache' => [
                    'driver' => config('cache.default'),
                ],
                'queue' => [
                    'driver' => config('queue.default'),
                    'connection' => config('queue.connections.' . config('queue.default')),
                ],
                'mail' => [
                    'driver' => config('mail.driver'),
                    'from' => config('mail.from'),
                ],
                'filesystems' => [
                    'default' => config('filesystems.default'),
                ],
                'ai_service' => [
                    'enabled' => config('services.ai.enabled', false),
                    'provider' => config('services.ai.provider'),
                    'endpoint' => config('services.ai.endpoint'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal get configuration', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil konfigurasi',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get audit trail (activity logs)
     * Dari Project.md: "melihat Audit Trail (Log Aktivitas)"
     */
    public function getAuditTrail(Request $request)
    {
        try {
            $perPage = min((int) $request->input('per_page', 50), 100);
            $action = $request->input('action', '');
            $userId = $request->input('user_id', '');
            $dateFrom = $request->input('date_from', '');
            $dateTo = $request->input('date_to', '');

            $query = DB::table('activity_logs')
                ->join('users', 'activity_logs.user_id', '=', 'users.id')
                ->select(
                    'activity_logs.id',
                    'activity_logs.user_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    'activity_logs.action',
                    'activity_logs.table_name',
                    'activity_logs.old_values',
                    'activity_logs.new_values',
                    'activity_logs.ip_address',
                    'activity_logs.created_at'
                );

            if ($action) {
                $query->where('activity_logs.action', 'like', "%{$action}%");
            }

            if ($userId) {
                $query->where('activity_logs.user_id', $userId);
            }

            if ($dateFrom) {
                $query->whereDate('activity_logs.created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('activity_logs.created_at', '<=', $dateTo);
            }

            $logs = $query->orderBy('activity_logs.created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs->items(),
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal get audit trail', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil audit trail',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Clear application cache
     * Dangerous operation - log it
     */
    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            $this->auditLogService->log(
                auth()->id(),
                'CLEAR_CACHE',
                'system',
                [],
                ['caches_cleared' => true]
            );

            return response()->json([
                'success' => true,
                'message' => 'Cache berhasil dibersihkan',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal clear cache', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan cache',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Run database migrations
     * Dangerous operation - log it
     */
    public function runMigrations(Request $request)
    {
        try {
            $request->validate([
                'step' => 'sometimes|boolean',
            ]);

            if ($request->input('step', false)) {
                Artisan::call('migrate', ['--step' => true]);
            } else {
                Artisan::call('migrate');
            }

            $this->auditLogService->log(
                auth()->id(),
                'RUN_MIGRATIONS',
                'system',
                [],
                ['migrations_ran' => true]
            );

            return response()->json([
                'success' => true,
                'message' => 'Migrations berhasil dijalankan',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal run migrations', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan migrations',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retry failed jobs
     */
    public function retryFailedJobs(Request $request)
    {
        try {
            Artisan::call('queue:retry all');

            $this->auditLogService->log(
                auth()->id(),
                'RETRY_FAILED_JOBS',
                'system',
                [],
                ['failed_jobs_retried' => true]
            );

            return response()->json([
                'success' => true,
                'message' => 'Failed jobs berhasil di-retry',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal retry failed jobs', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal retry failed jobs',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get application logs
     */
    public function getApplicationLogs(Request $request)
    {
        try {
            $lines = min((int) $request->input('lines', 100), 1000);
            $logFile = storage_path('logs/laravel.log');

            if (!file_exists($logFile)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Log file tidak ditemukan',
                ]);
            }

            $logs = array_slice(
                array_reverse(file($logFile)),
                0,
                $lines
            );

            return response()->json([
                'success' => true,
                'data' => $logs,
                'total_lines' => count(file($logFile)),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal get application logs', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil application logs',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'connected', 'driver' => config('database.default')];
        } catch (\Exception $e) {
            return ['status' => 'disconnected', 'error' => $e->getMessage()];
        }
    }

    private function checkCacheConnection()
    {
        try {
            cache()->put('test_key', 'test_value', 1);
            cache()->forget('test_key');
            return ['status' => 'working', 'driver' => config('cache.default')];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    private function checkStorageHealth()
    {
        try {
            $storagePath = storage_path();
            $diskSpace = disk_free_space($storagePath);
            $diskTotal = disk_total_space($storagePath);

            return [
                'status' => 'available',
                'free_space_mb' => $diskSpace / 1024 / 1024,
                'total_space_mb' => $diskTotal / 1024 / 1024,
                'usage_percent' => round(((
                    $diskTotal - $diskSpace) / $diskTotal) * 100, 2),
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    private function checkQueueStatus()
    {
        try {
            $queueDriver = config('queue.default');
            $jobCount = DB::table('jobs')->count();
            $failedCount = DB::table('failed_jobs')->count();

            return [
                'status' => 'available',
                'driver' => $queueDriver,
                'pending_jobs' => $jobCount,
                'failed_jobs' => $failedCount,
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    private function getDiskSpace()
    {
        try {
            $storagePath = storage_path();
            $free = disk_free_space($storagePath);
            $total = disk_total_space($storagePath);

            return [
                'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            return ['status' => 'error'];
        }
    }
}
