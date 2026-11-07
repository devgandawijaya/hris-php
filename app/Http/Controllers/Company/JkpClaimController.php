<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\JkpClaimCertification;
use App\Services\Compliance\JkpClaimService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class JkpClaimController extends Controller
{
    public function __construct(
        protected JkpClaimService $jkpService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $certifications = JkpClaimCertification::with(['employee.department'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('termination_date')
            ->paginate(10);

        $employees = Employee::where('company_id', $company->id)->orderBy('first_name')->get();

        return view('company.jkp.index', compact('company', 'certifications', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'termination_date' => 'required|date',
            'termination_reason_code' => 'required|string|max:50',
            'consecutive_paid_months' => 'required|integer|min:0|max:120',
            'total_paid_months_24m' => 'required|integer|min:0|max:120',
            'bpjs_tk_card_number' => 'nullable|string|max:50',
            'company_recommendation_letter' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        try {
            $certification = $this->jkpService->certifyClaim($employee, $validated);

            $statusMsg = $certification->eligible_for_jkp
                ? "Sertifikasi Hak JKP ({$certification->certificate_number}) berhasil diterbitkan dengan estimasi total manfaat tunai Rp " . number_format($certification->total_potential_cash_benefit, 0, ',', '.')
                : "Sertifikasi klaim JKP diterbitkan dengan catatan kepesertaan belum memenuhi masa iur statutori.";

            return redirect()->route('company.jkp.show', $certification->id)->with('success', $statusMsg);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(JkpClaimCertification $certification): View
    {
        $certification->load(['company', 'employee.department']);
        $disnakerData = $this->jkpService->generateDisnakerRecommendation($certification);

        return view('company.jkp.show', compact('certification', 'disnakerData'));
    }

    public function printRecommendation(JkpClaimCertification $certification): View
    {
        $certification->load(['company', 'employee.department']);
        $disnakerData = $this->jkpService->generateDisnakerRecommendation($certification);

        return view('company.jkp.print_recommendation', compact('certification', 'disnakerData'));
    }
}
