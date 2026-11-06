<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeWarning;
use App\Services\EmployeeRelations\DisciplinaryService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DisciplinaryController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected DisciplinaryService $disciplinaryService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');

        $query = EmployeeWarning::with(['employee.department', 'authorizedBy'])
            ->where('tenant_id', $tenant->id)
            ->latest('issued_date');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $warnings = $query->paginate(15);
        $employees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        $stats = [
            'total_warnings' => EmployeeWarning::where('tenant_id', $tenant->id)->count(),
            'active_warnings' => EmployeeWarning::where('tenant_id', $tenant->id)->where('status', 'active')->where('valid_until', '>=', now()->toDateString())->count(),
            'sp1_count' => EmployeeWarning::where('tenant_id', $tenant->id)->where('warning_level', 'sp1')->count(),
            'sp3_count' => EmployeeWarning::where('tenant_id', $tenant->id)->where('warning_level', 'sp3')->count(),
        ];

        return view('company.disciplinary.index', compact('warnings', 'employees', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'warning_level' => 'required|in:sp1,sp2,sp3',
            'issued_date' => 'required|date',
            'reason' => 'required|string',
            'action_plan' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $warning = $this->disciplinaryService->issueWarning($employee, $validated, Auth::user());

        return back()->with('success', "Surat Peringatan ({$warning->letter_number}) berhasil diterbitkan untuk {$employee->full_name}. Masa berlaku aktif 6 bulan.");
    }

    public function revoke(Request $request, EmployeeWarning $warning): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $this->disciplinaryService->revokeWarning($warning, $validated['reason'] ?? null);

        return back()->with('success', "Surat Peringatan {$warning->letter_number} berhasil dicabut.");
    }
}

