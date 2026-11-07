<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\EsgMetricSnapshot;
use App\Services\Compliance\EsgReportingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EsgReportingController extends Controller
{
    public function __construct(
        protected EsgReportingService $esgService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $selectedYear = (int) $request->input('year', date('Y'));

        $snapshots = EsgMetricSnapshot::where('company_id', $company->id)
            ->latest('reporting_year')
            ->get();

        $currentSnapshot = $snapshots->firstWhere('reporting_year', $selectedYear);

        return view('company.esg.index', compact(
            'company',
            'snapshots',
            'currentSnapshot',
            'selectedYear'
        ));
    }

    public function generateSnapshot(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'reporting_year' => 'required|integer|min:2020|max:2050',
        ]);

        $this->esgService->generateAnnualSnapshot($company, (int) $validated['reporting_year']);

        return redirect()->route('esg.index', ['year' => $validated['reporting_year']])
            ->with('success', "Snapshot Metrik ESG & Laporan Keberlanjutan Tahun {$validated['reporting_year']} berhasil diperbarui.");
    }

    public function printReport(EsgMetricSnapshot $snapshot): View
    {
        $snapshot->load('company');
        return view('company.esg.print_sustainability_report', compact('snapshot'));
    }
}
