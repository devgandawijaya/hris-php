<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Services\Contract\DigitalContractService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DigitalContractController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected DigitalContractService $contractService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $query = EmploymentContract::where('tenant_id', $tenant->id)
            ->with(['employee.department', 'employee.position'])
            ->latest('start_date');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('signed')) {
            $query->where('is_digitally_signed', $request->boolean('signed'));
        }

        $contracts = $query->paginate(15);

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'total' => EmploymentContract::where('tenant_id', $tenant->id)->count(),
            'signed' => EmploymentContract::where('tenant_id', $tenant->id)->where('is_digitally_signed', true)->count(),
            'pending_employee' => EmploymentContract::where('tenant_id', $tenant->id)->whereNull('employee_signed_at')->count(),
            'pending_company' => EmploymentContract::where('tenant_id', $tenant->id)->whereNull('company_signed_at')->count(),
        ];

        $summary = [
            'total' => $stats['total'],
            'pending_employee' => $stats['pending_employee'],
            'pending_company' => $stats['pending_company'],
            'fully_signed' => $stats['signed'],
        ];

        return view('company.contracts.digital_index', compact('company', 'contracts', 'employees', 'stats', 'summary'));
    }

    public function show(EmploymentContract $contract): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->first() ?? Company::first();
        $contract->load(['employee.department', 'employee.position']);
        return view('company.contracts.digital_show', compact('contract', 'company'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Normalize input fields for flexibility
        $input = $request->all();
        if (isset($input['contract_type']) && !isset($input['type'])) {
            $input['type'] = strtolower($input['contract_type']);
        }
        if (isset($input['basic_salary']) && !isset($input['monthly_wage'])) {
            $input['monthly_wage'] = $input['basic_salary'];
        }

        $validated = validator($input, [
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', 'in:pkwt,pkwtt,internship,probation'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'probation_end_date' => ['nullable', 'date'],
            'monthly_wage' => ['required', 'numeric', 'min:0'],
            'signer_name' => ['nullable', 'string', 'max:150'],
            'signer_position' => ['nullable', 'string', 'max:100'],
            'contract_body' => ['nullable', 'string'],
        ])->validate();

        $employee = Employee::findOrFail($validated['employee_id']);
        $contract = $this->contractService->draftContract($employee, $validated);

        return redirect()->route('company.digital-contracts.show', $contract)
            ->with('success', "Draft Perjanjian Kerja {$contract->contract_number} berhasil dibuat.");
    }

    public function signCompany(Request $request, EmploymentContract $contract): RedirectResponse
    {
        $signature = $request->input('company_signature') ?? $request->input('signature') ?? ('SIGN_COMPANY_' . time());

        $this->contractService->signByCompany(
            $contract,
            $signature,
            $request->user(),
            $request->ip()
        );

        return redirect()->back()->with('success', 'Perjanjian kerja berhasil ditandatangani secara digital oleh Perusahaan.');
    }

    public function print(EmploymentContract $contract): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->first() ?? Company::first();
        $contract->load(['employee.department', 'employee.position']);
        return view('company.contracts.digital_print', compact('contract', 'company'));
    }

    public function printContract(EmploymentContract $contract): View
    {
        return $this->print($contract);
    }
}
