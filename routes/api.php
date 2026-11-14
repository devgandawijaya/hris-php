<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\EmployeeApiController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\DepartmentApiController;
use App\Http\Controllers\Api\V1\LeaveApiController;
use App\Http\Controllers\Api\V1\PayrollApiController;
use App\Http\Controllers\Api\V1\WebhookApiController;
use App\Http\Controllers\Api\V1\OpenApiController;
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

// Phase 12: SaaS Midtrans Payment Gateway Webhook Notification
Route::post('/webhooks/midtrans', [App\Http\Controllers\Company\PaymentGatewayController::class, 'midtransWebhook'])->name('api.webhooks.midtrans');

// OpenAPI 3.1.0 Specification JSON Document (Public endpoint for Swagger/Postman/OpenAPI tools)
Route::get('/v1/openapi.json', [OpenApiController::class, 'spec'])->name('api.v1.openapi.json');

// Enterprise REST API v1 (Secured with X-API-KEY / Bearer Token)
Route::prefix('v1')->middleware(VerifyEnterpriseApiKey::class)->group(function () {
    // 1. Employees (Master Data & Inbound Sync)
    Route::get('/employees', [EmployeeApiController::class, 'index'])->name('api.v1.employees.index');
    Route::post('/employees', [EmployeeApiController::class, 'store'])->name('api.v1.employees.store');
    Route::get('/employees/{id}', [EmployeeApiController::class, 'show'])->name('api.v1.employees.show');
    Route::put('/employees/{id}', [EmployeeApiController::class, 'update'])->name('api.v1.employees.update');

    // 2. Attendance & Biometric Devices
    Route::get('/attendance', [AttendanceApiController::class, 'index'])->name('api.v1.attendance.index');
    Route::post('/attendance', [AttendanceApiController::class, 'store'])->name('api.v1.attendance.store');
    Route::post('/attendance/bulk', [AttendanceApiController::class, 'bulkStore'])->name('api.v1.attendance.bulk');

    // 3. Departments & Organizational Units
    Route::get('/departments', [DepartmentApiController::class, 'index'])->name('api.v1.departments.index');
    Route::post('/departments', [DepartmentApiController::class, 'store'])->name('api.v1.departments.store');

    // 4. Leave & Time Off
    Route::get('/leave/types', [LeaveApiController::class, 'types'])->name('api.v1.leave.types');
    Route::get('/leave/requests', [LeaveApiController::class, 'index'])->name('api.v1.leave.requests.index');
    Route::post('/leave/requests', [LeaveApiController::class, 'store'])->name('api.v1.leave.requests.store');

    // 5. Payroll & General Ledger Sync
    Route::get('/payroll/summary', [PayrollApiController::class, 'summary'])->name('api.v1.payroll.summary');

    // 6. Webhooks Catalog
    Route::get('/webhooks/events', [WebhookApiController::class, 'events'])->name('api.v1.webhooks.events');
});
