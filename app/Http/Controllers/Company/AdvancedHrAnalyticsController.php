<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Services\Analytics\AdvancedHrAnalyticsService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdvancedHrAnalyticsController extends Controller
{
    public function __construct(
        protected AdvancedHrAnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $selectedYear = (int) $request->get('year', date('Y'));

        $turnover = $this->analyticsService->getTurnoverMetrics($company, $selectedYear);
        $bradford = $this->analyticsService->getBradfordFactorMetrics($company, $selectedYear);
        $demographics = $this->analyticsService->getDemographics($company);
        $labor = $this->analyticsService->getLaborCostMetrics($company, $selectedYear);

        return view('company.analytics.advanced', compact(
            'company',
            'selectedYear',
            'turnover',
            'bradford',
            'demographics',
            'labor'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();
        $selectedYear = (int) $request->get('year', date('Y'));

        $csvData = $this->analyticsService->exportAdvancedMetricsCsv($company, $selectedYear);
        $fileName = "Executive_HR_Analytics_{$company->slug}_{$selectedYear}.csv";

        return response()->stream(function () use ($csvData) {
            echo $csvData;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
