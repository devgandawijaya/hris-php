<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ContinuousShiftRoster;
use App\Models\EmployeeShiftRosterAssignment;
use App\Services\Attendance\ContinuousShiftRosterService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContinuousShiftRosterController extends Controller
{
    public function __construct(
        protected ContinuousShiftRosterService $rosterService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $rosters = ContinuousShiftRoster::with(['department', 'assignments.employee'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(10);

        $assignments = EmployeeShiftRosterAssignment::with(['roster', 'employee.department'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->latest('effective_start_date')
            ->paginate(15);

        $departments = Department::where('company_id', $company->id)->orderBy('name')->get();
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        return view('company.rosters.index', compact('company', 'rosters', 'assignments', 'departments', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'cycle_days' => 'required|integer|min:7|max:90',
            'work_days_per_cycle' => 'required|integer|min:1|max:90',
            'off_days_per_cycle' => 'required|integer|min:1|max:90',
            'night_shift_allowance_rate' => 'required|numeric|min:0',
            'standby_allowance_rate' => 'required|numeric|min:0',
        ]);

        $roster = $this->rosterService->createRoster($company, $validated);

        return back()->with('success', "Pola Shift Roster ({$roster->roster_code}) berhasil dibuat sesuai Kepmenakertrans No. 233/2003.");
    }

    public function assignGroup(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'continuous_shift_roster_id' => 'required|exists:continuous_shift_rosters,id',
            'employee_id' => 'required|exists:employees,id',
            'group_name' => 'required|string|max:50',
            'effective_start_date' => 'required|date',
        ]);

        $roster = ContinuousShiftRoster::where('tenant_id', $tenantId)->findOrFail($validated['continuous_shift_roster_id']);
        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        $assignment = $this->rosterService->assignEmployeeToGroup($roster, $employee, $validated['group_name'], $validated['effective_start_date']);

        return back()->with('success', "Karyawan {$employee->full_name} berhasil ditugaskan ke {$assignment->group_name} pada roster {$roster->name}.");
    }
}
