<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\FringeBenefit;
use App\Services\Tax\FringeBenefitTaxService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FringeBenefitController extends Controller
{
    public function __construct(
        protected FringeBenefitTaxService $fringeService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $selectedYear = (int) $request->get('year', date('Y'));
        $selectedMonth = (int) $request->get('month', date('n'));

        $benefits = FringeBenefit::with(['employee.department'])
            ->where('company_id', $company->id)
            ->where('tax_year', $selectedYear)
            ->where('period_month', $selectedMonth)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $totals = [
            'total_gross_market' => (float) FringeBenefit::where('company_id', $company->id)->where('tax_year', $selectedYear)->where('period_month', $selectedMonth)->sum('gross_market_value'),
            'total_non_taxable' => (float) FringeBenefit::where('company_id', $company->id)->where('tax_year', $selectedYear)->where('period_month', $selectedMonth)->sum('non_taxable_threshold'),
            'total_taxable_natura' => (float) FringeBenefit::where('company_id', $company->id)->where('tax_year', $selectedYear)->where('period_month', $selectedMonth)->sum('taxable_natura_value'),
        ];

        return view('company.fringe_benefits.index', compact(
            'company',
            'benefits',
            'employees',
            'totals',
            'selectedYear',
            'selectedMonth'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'tax_year' => ['required', 'integer', 'min:2020'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'benefit_type' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:200'],
            'gross_market_value' => ['required', 'numeric', 'min:0'],
            'non_taxable_threshold' => ['nullable', 'numeric', 'min:0'],
            'fiscal_notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $benefit = $this->fringeService->recordBenefit($company, $employee, $validated);

        $statusNatura = $benefit->is_taxable_object ? 'Objek PPh 21' : 'Dikecualikan (Non-Objek)';
        return back()->with('success', "Fasilitas natura {$benefit->description} ({$statusNatura}) berhasil dicatat.");
    }
}
