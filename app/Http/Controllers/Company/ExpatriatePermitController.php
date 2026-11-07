<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ExpatriatePermit;
use App\Services\Compliance\ExpatriatePermitService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class ExpatriatePermitController extends Controller
{
    public function __construct(
        protected ExpatriatePermitService $expatriateService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $permits = ExpatriatePermit::with(['employee.department', 'companionEmployee'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('rptka_approval_date')
            ->paginate(10);

        $expiringPermits = $this->expatriateService->checkExpiringPermits($tenantId, 60);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        return view('company.expatriates.index', compact('company', 'permits', 'expiringPermits', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'rptka_number' => 'required|string|max:100',
            'rptka_approval_date' => 'required|date',
            'rptka_expiry_date' => 'required|date|after:rptka_approval_date',
            'job_position_title' => 'required|string|max:150',
            'passport_number' => 'required|string|max:50',
            'passport_expiry_date' => 'required|date',
            'itas_vitas_number' => 'nullable|string|max:100',
            'itas_expiry_date' => 'nullable|date',
            'dkptka_monthly_amount_usd' => 'required|numeric|min:0',
            'dkptka_payment_proof' => 'nullable|string|max:150',
            'companion_employee_id' => 'nullable|exists:employees,id',
            'technology_transfer_plan' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        try {
            $permit = $this->expatriateService->registerPermit($company, $employee, $validated);

            return back()->with('success', "Izin RPTKA ({$permit->rptka_number}) untuk {$employee->full_name} berhasil didaftarkan sesuai PP No. 34/2021.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(ExpatriatePermit $permit): View
    {
        $permit->load(['company', 'employee.department', 'companionEmployee.department']);
        return view('company.expatriates.show', compact('permit'));
    }

    public function updateStatus(Request $request, ExpatriatePermit $permit): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,expiring_soon,expired,repatriated',
        ]);

        $this->expatriateService->updatePermitStatus($permit, $validated['status']);

        return back()->with('success', "Status izin RPTKA diperbarui menjadi {$validated['status']}.");
    }
}
