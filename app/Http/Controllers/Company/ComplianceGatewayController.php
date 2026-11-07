<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BpjsComplianceReport;
use App\Models\PayrollPeriod;
use App\Models\RegionalMinimumWage;
use App\Services\Compliance\RegulatoryComplianceService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplianceGatewayController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected RegulatoryComplianceService $complianceService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $selectedCity = $request->input('city');

        $umkAudit = $this->complianceService->checkMinimumWageCompliance($tenant->id, $selectedCity);

        $umkStandards = RegionalMinimumWage::orderBy('province')->orderBy('city_or_regency')->get();

        $periods = PayrollPeriod::where('tenant_id', $tenant->id)
            ->whereIn('status', ['approved', 'paid'])
            ->latest('start_date')
            ->get();

        $bpjsReports = BpjsComplianceReport::with('payrollPeriod')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(10);

        return view('company.compliance.index', compact(
            'umkAudit',
            'umkStandards',
            'periods',
            'bpjsReports',
            'selectedCity'
        ));
    }

    public function storeUmk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'province' => 'required|string|max:100',
            'city_or_regency' => 'required|string|max:100',
            'year' => 'required|integer|min:2020|max:2050',
            'minimum_wage_amount' => 'required|numeric|min:100000',
            'legal_reference' => 'nullable|string|max:150',
        ]);

        RegionalMinimumWage::updateOrCreate(
            [
                'province' => $validated['province'],
                'city_or_regency' => $validated['city_or_regency'],
                'year' => (int) $validated['year'],
            ],
            [
                'minimum_wage_amount' => $validated['minimum_wage_amount'],
                'legal_reference' => $validated['legal_reference'] ?? 'PP No. 51 Tahun 2023',
            ]
        );

        return back()->with('success', "Standar UMK {$validated['city_or_regency']} ({$validated['year']}) berhasil disimpan.");
    }

    public function generateBpjsReport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'report_type' => 'required|in:bpjs_tk_sipp,bpjs_kes_edabu',
        ]);

        $period = PayrollPeriod::findOrFail($validated['payroll_period_id']);

        if ($validated['report_type'] === 'bpjs_tk_sipp') {
            $report = $this->complianceService->generateBpjsTkSippReport($period);
            $typeName = 'BPJS Ketenagakerjaan (SIPP Online)';
        } else {
            $report = $this->complianceService->generateBpjsKesEdabuReport($period);
            $typeName = 'BPJS Kesehatan (e-Dabu)';
        }

        return back()->with('success', "Laporan statutori {$typeName} untuk periode {$period->name} berhasil dikompilasi ({$report->total_employees} peserta).");
    }

    public function downloadBpjsCsv(BpjsComplianceReport $report): StreamedResponse
    {
        $period = $report->payrollPeriod;

        if ($report->report_type === 'bpjs_tk_sipp') {
            $content = $this->complianceService->exportBpjsTkCsv($period);
            $filename = "bpjs_tk_sipp_{$report->report_month}_{$period->id}.csv";
        } else {
            $content = $this->complianceService->exportBpjsKesCsv($period);
            $filename = "bpjs_kes_edabu_{$report->report_month}_{$period->id}.csv";
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($content) {
            echo $content;
        }, 200, $headers);
    }
}
