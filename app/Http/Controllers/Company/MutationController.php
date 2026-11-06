<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeMutation;
use App\Models\Position;
use App\Services\Career\CareerTransitionService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MutationController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected CareerTransitionService $careerService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $type = $request->query('type');

        $query = EmployeeMutation::with([
            'employee.department',
            'employee.position',
            'fromDepartment',
            'toDepartment',
            'fromPosition',
            'toPosition',
        ])
        ->where('tenant_id', $tenant->id)
        ->latest('effective_date');

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $mutations = $query->paginate(15);
        $employees = Employee::with(['department', 'position'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->get();
        $departments = Department::where('tenant_id', $tenant->id)->get();
        $positions = Position::where('tenant_id', $tenant->id)->get();

        $stats = [
            'total' => EmployeeMutation::where('tenant_id', $tenant->id)->count(),
            'promotions' => EmployeeMutation::where('tenant_id', $tenant->id)->where('type', 'promotion')->count(),
            'transfers' => EmployeeMutation::where('tenant_id', $tenant->id)->where('type', 'transfer')->count(),
            'demotions' => EmployeeMutation::where('tenant_id', $tenant->id)->where('type', 'demotion')->count(),
        ];

        return view('company.mutations.index', compact('mutations', 'employees', 'departments', 'positions', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:promotion,demotion,transfer,salary_adjustment',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_position_id' => 'nullable|exists:positions,id',
            'to_salary' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $this->careerService->recordTransition($employee, $validated, Auth::user());

        $typeLabel = match ($validated['type']) {
            'promotion' => 'Promosi',
            'demotion' => 'Demosi',
            'transfer' => 'Mutasi / Rotasi',
            'salary_adjustment' => 'Penyesuaian Gaji',
            default => 'Perubahan Karir',
        };

        return back()->with('success', "Surat Keputusan {$typeLabel} atas nama {$employee->full_name} berhasil diterbitkan dan data karyawan telah diperbarui.");
    }
}
