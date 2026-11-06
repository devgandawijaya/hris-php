<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Services\Contract\ContractService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractManagementController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected ContractService $contractService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');
        $type = $request->query('type');

        // Check and update expiring contracts status (<= 30 days)
        $this->contractService->updateExpiringContracts($tenant->id, 30);

        $query = EmploymentContract::with(['employee.department', 'employee.position'])
            ->where('tenant_id', $tenant->id)
            ->latest('start_date');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $contracts = $query->paginate(15);
        $employees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        $stats = [
            'total_active' => EmploymentContract::where('tenant_id', $tenant->id)->where('status', 'active')->count(),
            'expiring_soon' => EmploymentContract::where('tenant_id', $tenant->id)->where('status', 'expiring_soon')->count(),
            'total_compensation_due' => EmploymentContract::where('tenant_id', $tenant->id)->where('compensation_paid', false)->where('compensation_calculated', '>', 0)->sum('compensation_calculated'),
            'total_pkwtt' => EmploymentContract::where('tenant_id', $tenant->id)->where('type', 'pkwtt')->count(),
        ];

        return view('company.contracts.index', compact('contracts', 'employees', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'contract_number' => 'required|string|max:100',
            'type' => 'required|in:pkwt,pkwtt,internship,probation',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'probation_end_date' => 'nullable|date|after_or_equal:start_date',
            'monthly_wage' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'document' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);

        $contract = $this->contractService->createContract($employee, $validated, $request->file('document'));

        return back()->with('success', "Kontrak kerja #{$contract->contract_number} berhasil dibuat. Kompensasi PP 35/2021 dihitung otomatis sebesar Rp " . number_format($contract->compensation_calculated, 2, ',', '.') . ".");
    }

    public function renew(Request $request, EmploymentContract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'contract_number' => 'required|string|max:100',
            'type' => 'required|in:pkwt,pkwtt,internship,probation',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'monthly_wage' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'document' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $newContract = $this->contractService->renewContract($contract, $validated, $request->file('document'));

        return back()->with('success', "Perpanjangan kontrak #{$newContract->contract_number} berhasil diproses (Pembaruan ke-{$newContract->renewal_count}).");
    }

    public function markCompensation(EmploymentContract $contract): RedirectResponse
    {
        $this->contractService->markCompensationPaid($contract);

        return back()->with('success', 'Uang kompensasi PKWT per PP 35/2021 berhasil ditandai Lunas dibayarkan kepada pekerja.');
    }
}

