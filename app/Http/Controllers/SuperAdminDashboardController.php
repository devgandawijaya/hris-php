<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Employee;
use App\Models\Subscription;
use App\Models\AuditLog;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalCompanies = Company::withoutGlobalScopes()->count();
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $trialTenants = Tenant::where('status', 'trial')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        $totalUsers = User::count();
        $totalEmployees = Employee::withoutGlobalScopes()->count();

        $recentTenants = Tenant::with(['companies', 'activeSubscription.plan'])
            ->latest()
            ->take(6)
            ->get();

        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        return view('super-admin.dashboard', compact(
            'totalCompanies',
            'totalTenants',
            'activeTenants',
            'trialTenants',
            'suspendedTenants',
            'totalUsers',
            'totalEmployees',
            'recentTenants',
            'recentAuditLogs'
        ));
    }
}

