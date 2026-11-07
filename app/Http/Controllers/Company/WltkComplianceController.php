<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\WltkReport;
use App\Services\Compliance\WltkComplianceService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class WltkComplianceController extends Controller
{
    public function __construct(
        protected WltkComplianceService $wltkService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $reports = WltkReport::where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('reporting_year')
            ->paginate(10);

        $renewalAlerts = $this->wltkService->checkRenewalDueAlerts($tenantId, 60);

        return view('company.wltk.index', compact('company', 'reports', 'renewalAlerts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'reporting_year' => 'required|integer|min:2020|max:2035',
            'report_period' => 'required|string|in:annual,establishment,relocation,closure',
        ]);

        $report = $this->wltkService->generateDraftReport(
            $company,
            (int) $validated['reporting_year'],
            $validated['report_period']
        );

        return redirect()->route('company.wltk.show', $report->id)
            ->with('success', "Draft Laporan WLTK Tahun {$report->reporting_year} berhasil dibuat dengan nomor {$report->report_number}.");
    }

    public function show(WltkReport $report): View
    {
        return view('company.wltk.show', compact('report'));
    }

    public function submitReceipt(Request $request, WltkReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'disnaker_receipt_number' => 'required|string|max:100',
            'submission_date' => 'required|date',
        ]);

        $this->wltkService->markSubmitted(
            $report,
            $validated['disnaker_receipt_number'],
            $validated['submission_date']
        );

        return back()->with('success', "Laporan WLTK berhasil dicatat telah diserahkan ke Kemnaker/Disnaker dengan nomor tanda terima {$validated['disnaker_receipt_number']}.");
    }

    public function exportJson(WltkReport $report): Response
    {
        $data = $this->wltkService->exportKemnakerJson($report);
        $fileName = sprintf('WLTK_%s_%s.json', $report->reporting_year, preg_replace('/[^A-Za-z0-9]/', '_', $report->report_number));

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function exportCsv(WltkReport $report): Response
    {
        $csv = $this->wltkService->exportKemnakerCsv($report);
        $fileName = sprintf('WLTK_%s_%s.csv', $report->reporting_year, preg_replace('/[^A-Za-z0-9]/', '_', $report->report_number));

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
