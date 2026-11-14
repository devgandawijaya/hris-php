<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApiController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function types(): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $types = LeaveType::where('tenant_id', $tenant->id)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $types->count(),
            'data' => $types,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $query = LeaveRequest::where('tenant_id', $tenant->id)
            ->with(['employee.department', 'leaveType']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('employee_number')) {
            $empNumber = $request->query('employee_number');
            $query->whereHas('employee', function ($q) use ($empNumber) {
                $q->where('employee_number', $empNumber);
            });
        }

        $requests = $query->latest()->paginate($request->query('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'total' => $requests->total(),
                'per_page' => $requests->perPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_number' => 'required|string',
            'leave_type_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)
            ->where('employee_number', $validated['employee_number'])
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => "Employee with NIK '{$validated['employee_number']}' not found.",
            ], 404);
        }

        $leaveType = LeaveType::where('tenant_id', $tenant->id)
            ->where('id', $validated['leave_type_id'])
            ->first();

        if (!$leaveType) {
            return response()->json([
                'success' => false,
                'message' => "Leave type ID {$validated['leave_type_id']} not found.",
            ], 404);
        }

        $start = strtotime($validated['start_date']);
        $end = strtotime($validated['end_date']);
        $days = (int) round(($end - $start) / (60 * 60 * 24)) + 1;

        $leaveRequest = LeaveRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $days,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Permohonan cuti {$days} hari berhasil dibuat.",
            'data' => $leaveRequest->load(['employee', 'leaveType']),
        ], 201);
    }
}
