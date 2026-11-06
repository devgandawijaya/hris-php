<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\TaxReconciliation;
use App\Services\Compliance\TaxReconciliationService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxReconciliationController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected TaxReconciliationService $reconciliationService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $selectedYear = (int) $request->input('year', date('Y'));

        $records = TaxReconciliation::with(['employee.department', 'employee.position'])
            ->where('tenant_id', $tenant->id)
            ->where('tax_year', $selectedYear)
            ->get();

        $stats = [
            'total_employees' => $records->count(),
            'total_gross_annual' => $records->sum('gross_income_annual'),
            'total_annual_tax_due' => $records->sum('pph21_annual_due'),
            'total_ter_paid' => $records->sum('pph21_ter_paid_total'),
            'total_december_tax' => $records->sum('pph21_december_tax'),
        ];

        return view('company.payroll.tax_reconciliation', compact('records', 'selectedYear', 'stats'));
    }

    public function reconcile(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
        ]);

        $result = $this->reconciliationService->reconcileTenant($tenant->id, (int) $validated['year']);

        return back()->with('success', "Rekonsiliasi PPh 21 Masa Terakhir (Formulir 1721-A1) Tahun Pajak {$result['tax_year']} berhasil diselesaikan untuk {$result['reconciled_employees']} karyawan. Total PPh 21 Terutang Setahun: Rp " . number_format($result['total_annual_tax'], 2, ',', '.') . ".");
    }

    public function show(TaxReconciliation $reconciliation): View
    {
        $reconciliation->load(['employee.department', 'employee.position', 'employee.company']);
        $company = $reconciliation->employee->company;

        return view('company.payroll.1721_a1', compact('reconciliation', 'company'));
    }
}
