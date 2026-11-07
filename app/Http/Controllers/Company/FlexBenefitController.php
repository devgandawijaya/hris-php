<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\FlexBenefitAllocation;
use App\Models\FlexBenefitCategory;
use App\Models\FlexBenefitClaim;
use App\Services\Financial\FlexBenefitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class FlexBenefitController extends Controller
{
    public function __construct(
        protected FlexBenefitService $flexService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $selectedYear = (int) $request->input('year', date('Y'));

        $categories = FlexBenefitCategory::where('company_id', $company->id)->get();
        $allocations = FlexBenefitAllocation::with(['employee.department', 'employee.position', 'jobGrade'])
            ->where('company_id', $company->id)
            ->where('period_year', $selectedYear)
            ->latest()
            ->paginate(15);

        $recentClaims = FlexBenefitClaim::with(['employee', 'category', 'allocation'])
            ->where('tenant_id', $tenantId ?? $company->tenant_id)
            ->latest()
            ->paginate(10);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();
        $categoryBreakdown = $this->flexService->getCategoryUsageBreakdown($company->tenant_id, $company->id, $selectedYear);

        $stats = [
            'total_allocated_points' => FlexBenefitAllocation::where('company_id', $company->id)->where('period_year', $selectedYear)->sum('total_points_allocated'),
            'total_points_used' => FlexBenefitAllocation::where('company_id', $company->id)->where('period_year', $selectedYear)->sum('points_used'),
            'total_claims_count' => FlexBenefitClaim::where('tenant_id', $company->tenant_id)->whereYear('claim_date', $selectedYear)->count(),
        ];

        return view('company.flex_benefits.index', compact(
            'company',
            'categories',
            'allocations',
            'recentClaims',
            'employees',
            'selectedYear',
            'categoryBreakdown',
            'stats'
        ));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        FlexBenefitCategory::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('flex-benefits.index')->with('success', 'Kategori Benefit Fleksibel berhasil ditambahkan.');
    }

    public function storeAllocation(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_year' => 'required|integer|min:2020|max:2050',
            'total_points_allocated' => 'required|numeric|min:100000',
        ]);

        $this->flexService->allocatePoints(
            $company->tenant_id,
            $company->id,
            (int) $validated['employee_id'],
            (int) $validated['period_year'],
            (float) $validated['total_points_allocated']
        );

        return redirect()->route('flex-benefits.index')->with('success', 'Alokasi plafon poin flex-benefit berhasil diberikan.');
    }

    public function storeClaim(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'flex_benefit_allocation_id' => 'required|exists:flex_benefit_allocations,id',
            'flex_benefit_category_id' => 'required|exists:flex_benefit_categories,id',
            'claim_amount' => 'required|numeric|min:1000',
            'claim_date' => 'required|date',
            'receipt_file_url' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $allocation = FlexBenefitAllocation::findOrFail($validated['flex_benefit_allocation_id']);

        try {
            $this->flexService->submitClaim(
                $allocation,
                (int) $validated['flex_benefit_category_id'],
                (float) $validated['claim_amount'],
                $validated['claim_date'],
                $validated['receipt_file_url'] ?? null,
                $validated['notes'] ?? null
            );

            return redirect()->route('flex-benefits.index')->with('success', 'Klaim fleksibel benefit berhasil diajukan untuk verifikasi.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function approveClaim(FlexBenefitClaim $claim): RedirectResponse
    {
        $userId = auth()->id() ?? 1;

        try {
            $this->flexService->approveClaim($claim, $userId);
            return redirect()->route('flex-benefits.index')->with('success', "Klaim {$claim->claim_number} berhasil disetujui dan poin dipotong.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectClaim(Request $request, FlexBenefitClaim $claim): RedirectResponse
    {
        $userId = auth()->id() ?? 1;
        $reason = $request->input('rejection_reason', 'Dokumen/bukti kuitansi tidak memenuhi ketentuan.');

        $this->flexService->rejectClaim($claim, $userId, $reason);
        return redirect()->route('flex-benefits.index')->with('success', "Klaim {$claim->claim_number} berhasil ditolak.");
    }
}
