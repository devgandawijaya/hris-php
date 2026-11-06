<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\EmployeeApiController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Middleware\VerifyEnterpriseApiKey;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Enterprise REST API v1
Route::prefix('v1')->middleware(VerifyEnterpriseApiKey::class)->group(function () {
    Route::get('/employees', [EmployeeApiController::class, 'index'])->name('api.v1.employees.index');
    Route::get('/employees/{id}', [EmployeeApiController::class, 'show'])->name('api.v1.employees.show');
    Route::get('/attendance', [AttendanceApiController::class, 'index'])->name('api.v1.attendance.index');
    Route::post('/attendance', [AttendanceApiController::class, 'store'])->name('api.v1.attendance.store');
});
