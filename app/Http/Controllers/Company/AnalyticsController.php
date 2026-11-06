<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Services\Analytics\HrAnalyticsService;
use App\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected HrAnalyticsService $analyticsService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();
        $metrics = $this->analyticsService->getExecutiveMetrics($tenant->id);
        $payrollPeriods = PayrollPeriod::where('tenant_id', $tenant->id)->latest('start_date')->get();

        return view('company.analytics.index', compact('metrics', 'payrollPeriods'));
    }

    public function exportHeadcount(): StreamedResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $csv = $this->analyticsService->exportHeadcountCsv($tenant->id);
        $filename = 'rihris_laporan_headcount_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportAttendance(Request $request): StreamedResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $month = $request->query('month');
        $csv = $this->analyticsService->exportAttendanceCsv($tenant->id, $month);
        $filename = 'rihris_laporan_presensi_' . ($month ?: date('Y-m')) . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPayroll(Request $request): StreamedResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $periodId = $request->query('period_id') ? (int) $request->query('period_id') : null;
        $csv = $this->analyticsService->exportPayrollSummaryCsv($tenant->id, $periodId);
        $filename = 'rihris_laporan_payroll_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
