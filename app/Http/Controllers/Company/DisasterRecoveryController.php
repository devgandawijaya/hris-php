<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DisasterRecoveryConfig;
use App\Models\DisasterRecoveryDrillLog;
use App\Services\Infrastructure\DisasterRecoveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisasterRecoveryController extends Controller
{
    public function __construct(
        protected DisasterRecoveryService $drService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $config = $this->drService->getOrCreateConfig($company);

        $drills = DisasterRecoveryDrillLog::where('company_id', $company->id)
            ->latest('drill_date')
            ->paginate(10);

        $stats = [
            'total_drills' => DisasterRecoveryDrillLog::where('company_id', $company->id)->count(),
            'passed_drills' => DisasterRecoveryDrillLog::where('company_id', $company->id)->where('status', 'passed')->count(),
            'replication_lag_ms' => $config->current_replication_lag_ms,
            'rpo_target' => $config->rpo_target_minutes,
            'rto_target' => $config->rto_target_minutes,
        ];

        return view('company.dr.index', compact('company', 'config', 'drills', 'stats'));
    }

    public function storeDrill(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $config = $this->drService->getOrCreateConfig($company);

        $validated = $request->validate([
            'scenario_type' => 'required|string|max:100',
            'actual_failover_duration_seconds' => 'required|integer|min:1',
            'data_loss_records_count' => 'required|integer|min:0',
            'lead_auditor_name' => 'required|string|max:100',
        ]);

        $this->drService->executeDrillRun(
            $config,
            $validated['scenario_type'],
            (int) $validated['actual_failover_duration_seconds'],
            (int) $validated['data_loss_records_count'],
            $validated['lead_auditor_name']
        );

        return redirect()->route('disaster-recovery.index')->with('success', 'Hasil latihan Disaster Recovery Drill berhasil dicatat dan diaudit.');
    }

    public function failover(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $config = $this->drService->getOrCreateConfig($company);
        $this->drService->triggerFailover($config);

        return redirect()->route('disaster-recovery.index')->with('success', "Emergency Failover berhasil dialihkan ke {$config->primary_region}.");
    }
}
