<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentApiController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function index(): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $departments = Department::where('tenant_id', $tenant->id)
            ->withCount('employees')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $departments->count(),
            'data' => $departments,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments')->where('tenant_id', $tenant->id),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        $department = Department::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Department '{$department->name}' created successfully.",
            'data' => $department,
        ], 201);
    }
}
