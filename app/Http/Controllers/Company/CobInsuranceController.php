<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CobInsurancePolicy;
use App\Models\Company;
use App\Models\Employee;
use App\Services\Compliance\CobInsuranceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CobInsuranceController extends Controller
{
    public function __construct(
        protected CobInsuranceService $cobService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $policies = CobInsurancePolicy::with(['employee.department', 'employee.position'])
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        $stats = [
            'total_policies' => $policies->total(),
            'total_coverage_pool' => CobInsurancePolicy::where('company_id', $company->id)->sum('annual_limit_amount'),
            'total_usage_amount' => CobInsurancePolicy::where('company_id', $company->id)->sum('current_usage_amount'),
            'active_insured_count' => CobInsurancePolicy::where('company_id', $company->id)->where('status', 'active')->count(),
        ];

        return view('company.cob.index', compact('company', 'policies', 'employees', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'insurance_company_name' => 'required|string|max:150',
            'policy_number' => 'required|string|max:100',
            'bpjs_health_card_number' => 'required|string|max:50',
            'plan_type' => 'required|string|max:50',
            'annual_limit_amount' => 'required|numeric|min:1000000',
            'cob_claim_mechanism' => 'required|string|in:bpjs_first_payer,direct_cashless',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $this->cobService->registerPolicy(
            $employee,
            $validated['insurance_company_name'],
            $validated['policy_number'],
            $validated['bpjs_health_card_number'],
            $validated['plan_type'],
            (float) $validated['annual_limit_amount'],
            $validated['cob_claim_mechanism']
        );

        return redirect()->route('cob-insurance.index')->with('success', 'Polis Asuransi Komersial CoB BPJS Kesehatan berhasil didaftarkan.');
    }

    public function coordinateClaim(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cob_policy_id' => 'required|exists:cob_insurance_policies,id',
            'total_hospital_bill' => 'required|numeric|min:1000',
            'bpjs_covered_amount' => 'required|numeric|min:0',
        ]);

        $policy = CobInsurancePolicy::findOrFail($validated['cob_policy_id']);

        try {
            $result = $this->cobService->coordinateClaim(
                $policy,
                (float) $validated['total_hospital_bill'],
                (float) $validated['bpjs_covered_amount'],
                true
            );

            return back()->with('success', sprintf(
                'Koordinasi Manfaat CoB Berhasil: Total Tagihan Rp %s | Ditanggung BPJS: Rp %s | Ditanggung Asuransi Top-up: Rp %s | Iur Biaya Pasien: Rp %s.',
                number_format($result['total_hospital_bill'], 0, ',', '.'),
                number_format($result['bpjs_first_payer_paid'], 0, ',', '.'),
                number_format($result['commercial_topup_paid'], 0, ',', '.'),
                number_format($result['patient_copay_amount'], 0, ',', '.')
            ));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
