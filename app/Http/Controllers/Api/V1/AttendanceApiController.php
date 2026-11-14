<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $date = $request->query('date', now()->toDateString());

        $records = AttendanceRecord::where('tenant_id', $tenant->id)
            ->where('date', $date)
            ->with(['employee.department', 'employee.position'])
            ->get();

        return response()->json([
            'success' => true,
            'date' => $date,
            'count' => $records->count(),
            'data' => $records,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_number' => 'required|string',
            'type' => 'required|in:clock_in,clock_out',
            'timestamp' => 'required|date',
            'device_id' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)
            ->where('employee_number', $validated['employee_number'])
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => "Employee with NIK {$validated['employee_number']} not found.",
            ], 404);
        }

        $date = date('Y-m-d', strtotime($validated['timestamp']));
        $time = date('H:i:s', strtotime($validated['timestamp']));

        $record = AttendanceRecord::firstOrNew([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'date' => $date,
        ]);

        if ($validated['type'] === 'clock_in') {
            $record->clock_in = $time;
            $record->status = 'present';
        } else {
            $record->clock_out = $time;
        }

        $record->notes = 'Synced from Biometric Machine ID: ' . ($validated['device_id'] ?? 'GATE-01');
        $record->save();

        return response()->json([
            'success' => true,
            'message' => "Attendance {$validated['type']} recorded successfully.",
            'data' => $record,
        ], 201);
    }

    /**
     * Bulk biometric attendance records push.
     * Useful for biometric fingerprint / face terminals uploading batch offline logs.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'device_id' => 'nullable|string|max:100',
            'logs' => 'required|array|min:1|max:500',
            'logs.*.employee_number' => 'required|string',
            'logs.*.type' => 'required|in:clock_in,clock_out',
            'logs.*.timestamp' => 'required|date',
            'logs.*.device_id' => 'nullable|string',
        ]);

        $deviceId = $validated['device_id'] ?? 'BIOMETRIC-TERMINAL-DEFAULT';
        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['logs'] as $index => $item) {
            $employee = Employee::where('tenant_id', $tenant->id)
                ->where('employee_number', $item['employee_number'])
                ->first();

            if (!$employee) {
                $failed++;
                $errors[] = "Row {$index}: Employee NIK '{$item['employee_number']}' not found.";
                continue;
            }

            $date = date('Y-m-d', strtotime($item['timestamp']));
            $time = date('H:i:s', strtotime($item['timestamp']));

            $record = AttendanceRecord::firstOrNew([
                'tenant_id' => $tenant->id,
                'employee_id' => $employee->id,
                'date' => $date,
            ]);

            if ($item['type'] === 'clock_in') {
                $record->clock_in = $time;
                $record->status = 'present';
            } else {
                $record->clock_out = $time;
            }

            $specificDevice = $item['device_id'] ?? $deviceId;
            $record->notes = "Batch Synced from {$specificDevice}";
            $record->save();

            $successful++;
        }

        return response()->json([
            'success' => $failed === 0,
            'message' => "Batch presensi diproses: {$successful} berhasil, {$failed} gagal.",
            'summary' => [
                'total' => count($validated['logs']),
                'successful' => $successful,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ], 200);
    }
}
