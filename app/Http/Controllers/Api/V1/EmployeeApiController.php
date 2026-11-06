<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $employees = Employee::where('tenant_id', $tenant->id)
            ->with(['department', 'position'])
            ->paginate($request->query('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'total' => $employees->total(),
                'per_page' => $employees->perPage(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $employee = Employee::where('tenant_id', $tenant->id)
            ->with(['department', 'position', 'contracts'])
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('employee_number', $id);
            })
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }
}
