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
            ->with('employee')
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
}
