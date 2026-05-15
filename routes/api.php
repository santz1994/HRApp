<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeImportExportController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route menggunakan prefix /api
|
*/

// ============================================
// Authentication Routes (Public)
// ============================================
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// ============================================
// Protected Routes (Require Authentication)
// ============================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth Routes ---
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // --- Department Routes (HR, IT) ---
    Route::middleware('checkAnyRole:hr,it')->group(function () {
        Route::apiResource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // --- Position Routes (HR, IT) ---
    Route::middleware('checkAnyRole:hr,it')->group(function () {
        Route::apiResource('positions', PositionController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // --- Employee Routes ---
    Route::prefix('employees')->group(function () {
        // Read endpoints (Dashboard, Analytics, List) - HR, Director, Admin Dept, IT
        Route::middleware('checkAnyRole:hr,director,admin_department,it')->group(function () {
            Route::get('/', [EmployeeController::class, 'index']);
            Route::get('/statistics', [EmployeeController::class, 'statistics']);
            Route::get('/{id}', [EmployeeController::class, 'show']);
        });

        // Write endpoints (CRUD) - HR and IT
        Route::middleware('checkAnyRole:hr,it')->group(function () {
            Route::post('/', [EmployeeController::class, 'store']);
            Route::put('/{id}', [EmployeeController::class, 'update']);
            Route::delete('/{id}', [EmployeeController::class, 'destroy']);
            Route::get('/{id}/id-card', [EmployeeController::class, 'printIdCard']);
        });

        // Import/Export - HR and IT
        Route::prefix('import-export')->middleware('checkAnyRole:hr,it')->group(function () {
            Route::get('/export', [EmployeeImportExportController::class, 'export']);
            Route::post('/import', [EmployeeImportExportController::class, 'import']);
            Route::get('/template', [EmployeeImportExportController::class, 'getTemplate']);
        });

        // File Upload - HR and IT
        Route::post('/upload-employees', [FileUploadController::class, 'uploadEmployees'])
            ->middleware('checkAnyRole:hr,it');

        Route::get('/import-status', [FileUploadController::class, 'importStatus'])
            ->middleware('checkAnyRole:hr,it');
    });

    // --- Attendance Routes - HR, IT, Admin Dept ---
    Route::prefix('attendances')->middleware('checkAnyRole:hr,it,admin_department')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/', [AttendanceController::class, 'store']);
        Route::get('/summary/{employeeId}', [AttendanceController::class, 'summary']);
    });

    // --- Medical Record Routes - HR, IT, Admin Dept ---
    Route::prefix('medical-records')->middleware('checkAnyRole:hr,it,admin_department')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'index']);
        Route::post('/', [MedicalRecordController::class, 'store']);
        Route::delete('/{id}', [MedicalRecordController::class, 'destroy']);
    });
});