<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\FwaSchedule;
use App\Services\Attendance\FwaService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FwaController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected FwaService $fwaService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $query = FwaSchedule::where('company_id', $company->id)
            ->with(['employee.department', 'approver'])
            ->latest('date');

        if ($request->filled('mode')) {
            $query->where('work_mode', $request->input('mode'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $schedules = $query->paginate(20);

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'total_wfh' => FwaSchedule::where('company_id', $company->id)->where('work_mode', 'wfh')->count(),
            'pending' => FwaSchedule::where('company_id', $company->id)->where('status', 'pending')->count(),
            'approved_today' => FwaSchedule::where('company_id', $company->id)->where('date', now()->toDateString())->where('work_mode', 'wfh')->where('status', 'approved')->count(),
        ];

        $summary = [
            'total_schedules' => FwaSchedule::where('company_id', $company->id)->count(),
            'approved_wfh' => FwaSchedule::where('company_id', $company->id)->where('work_mode', 'wfh')->where('status', 'approved')->count(),
            'approved_wfo' => FwaSchedule::where('company_id', $company->id)->where('work_mode', 'wfo')->where('status', 'approved')->count(),
            'pending_approval' => $stats['pending'],
        ];

        return view('company.fwa.index', compact('company', 'schedules', 'employees', 'stats', 'summary'));
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $request->all();
        if (isset($input['work_date']) && !isset($input['date'])) {
            $input['date'] = $input['work_date'];
        }
        if (isset($input['arrangement_type']) && !isset($input['work_mode'])) {
            $mode = strtolower($input['arrangement_type']);
            $input['work_mode'] = ($mode === 'wfo') ? 'wfo' : 'wfh';
        }

        $validated = validator($input, [
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'work_mode' => ['required', 'in:wfh,wfo'],
            'reason' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $employee = Employee::findOrFail($validated['employee_id']);
        $schedule = $this->fwaService->requestFwa($employee, $validated['date'], $validated['work_mode'], $validated['reason'] ?? null);

        // Auto approve if entered by admin
        $this->fwaService->processApproval($schedule, $request->user(), true);

        return redirect()->back()->with('success', "Jadwal kerja {$validated['work_mode']} untuk {$employee->full_name} berhasil dijadwalkan.");
    }

    public function approve(Request $request, FwaSchedule $schedule): RedirectResponse
    {
        $isApproved = true;
        if ($request->has('approved')) {
            $isApproved = filter_var($request->input('approved'), FILTER_VALIDATE_BOOLEAN);
        } elseif ($request->has('status')) {
            $isApproved = strtoupper($request->input('status')) === 'APPROVED';
        }

        $this->fwaService->processApproval($schedule, $request->user(), $isApproved);

        $statusMsg = $isApproved ? 'disetujui' : 'ditolak';
        return redirect()->back()->with('success', "Permohonan jadwal kerja {$schedule->work_mode} berhasil {$statusMsg}.");
    }
}
