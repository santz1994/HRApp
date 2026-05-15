<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log(string $action, string $description, $model = null)
    {
        ActivityLog::create([
            'user_id' => Auth::id() ?? 1, // Fallback ke system admin jika via CLI/Queue
            'action' => strtoupper($action),
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}