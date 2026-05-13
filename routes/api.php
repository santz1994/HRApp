<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeImportExportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Employee Routes - Accessible by HR and Director (Read-only for Director)
    Route::prefix('employees')->group(function () {
        // Read endpoints - HR and Director
        Route::middleware('checkAnyRole:hr,director')->group(function () {
            Route::get('/', [EmployeeController::class, 'index']);
            Route::get('/statistics', [EmployeeController::class, 'statistics']);
            Route::get('/{id}', [EmployeeController::class, 'show']);
        });

        // Write endpoints - HR only
        Route::middleware('checkRole:hr')->group(function () {
            Route::post('/', [EmployeeController::class, 'store']);
            Route::put('/{id}', [EmployeeController::class, 'update']);
            Route::delete('/{id}', [EmployeeController::class, 'destroy']);
        });

        // Import/Export - HR only
        Route::prefix('import-export')->middleware('checkRole:hr')->group(function () {
            Route::get('/export', [EmployeeImportExportController::class, 'export']);
            Route::post('/import', [EmployeeImportExportController::class, 'import']);
            Route::get('/template', [EmployeeImportExportController::class, 'getTemplate']);
        });
    });
});
