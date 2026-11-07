<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\PlatformHealthService;
use Illuminate\View\View;

class PlatformHealthController extends Controller
{
    public function __construct(
        protected PlatformHealthService $healthService
    ) {}

    public function index(): View
    {
        $financials = $this->healthService->getFinancialMetrics();
        $system = $this->healthService->getSystemHealth();

        return view('super-admin.health.index', compact('financials', 'system'));
    }
}
