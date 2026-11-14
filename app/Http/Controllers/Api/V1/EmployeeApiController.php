<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeApiController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $query = Employee::where('tenant_id', $tenant->id)
            ->with(['department', 'position']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate($request->query('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'total' => $employees->total(),
                'per_page' => $employees->perPage(),
                'last_page' => $employees->lastPage(),
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

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees')->where('tenant_id', $tenant->id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female',
            'employment_status' => 'nullable|in:pkwtt,pkwt,probation,internship',
            'join_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:join_date',
            'department_id' => 'nullable|integer',
            'position_id' => 'nullable|integer',
            'basic_salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,suspended,resigned,terminated',
        ]);

        $companyId = Company::where('tenant_id', $tenant->id)->value('id') ?? 1;

        $validated['tenant_id'] = $tenant->id;
        $validated['company_id'] = $companyId;
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['employment_status'] = $validated['employment_status'] ?? 'pkwt';
        $validated['gender'] = $validated['gender'] ?? 'male';

        $employee = Employee::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee {$employee->full_name} ({$employee->employee_number}) created successfully.",
            'data' => $employee->load(['department', 'position']),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $employee = Employee::where('tenant_id', $tenant->id)
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

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female',
            'department_id' => 'nullable|integer',
            'position_id' => 'nullable|integer',
            'employment_status' => 'nullable|in:pkwtt,pkwt,probation,internship',
            'basic_salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,suspended,resigned,terminated',
        ]);

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee {$employee->employee_number} updated successfully.",
            'data' => $employee->fresh(['department', 'position']),
        ]);
    }
}
