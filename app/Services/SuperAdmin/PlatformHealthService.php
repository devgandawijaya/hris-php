<?php

namespace App\Services\SuperAdmin;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Employee;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class PlatformHealthService
{
    /**
     * Compute platform-wide SaaS financial metrics (MRR, ARR, ARPU).
     */
    public function getFinancialMetrics(): array
    {
        $activeSubscriptions = Subscription::with('plan')
            ->whereIn('status', ['active', 'trial'])
            ->get();

        $mrr = 0.00;
        $planBreakdown = [];

        foreach ($activeSubscriptions as $sub) {
            $plan = $sub->plan;
            if (!$plan) continue;

            $planName = $plan->name;
            $planBreakdown[$planName] = ($planBreakdown[$planName] ?? 0) + 1;

            if ($sub->status === 'active') {
                if ($sub->billing_cycle === 'yearly' && $plan->price_yearly > 0) {
                    $mrr += (float) ($plan->price_yearly / 12);
                } else {
                    $mrr += (float) $plan->price_monthly;
                }
            }
        }

        $arr = $mrr * 12;
        $activeTenantCount = Tenant::where('status', 'active')->count();
        $arpu = $activeTenantCount > 0 ? ($mrr / $activeTenantCount) : 0.00;

        $paidInvoicesSum = (float) SubscriptionInvoice::where('status', 'paid')->sum('amount');
        $unpaidInvoicesSum = (float) SubscriptionInvoice::where('status', 'pending')->sum('amount');

        return [
            'mrr' => round($mrr, 2),
            'arr' => round($arr, 2),
            'arpu' => round($arpu, 2),
            'paid_invoices_sum' => $paidInvoicesSum,
            'unpaid_invoices_sum' => $unpaidInvoicesSum,
            'plan_breakdown' => $planBreakdown,
            'total_plans' => SubscriptionPlan::count(),
        ];
    }

    /**
     * Inspect system and database resource health.
     */
    public function getSystemHealth(): array
    {
        $dbConnected = true;
        $dbVersion = 'Unknown';
        try {
            $pdo = DB::connection()->getPdo();
            $dbVersion = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $storageWritable = is_writable(storage_path());

        // Approximate row counts for major enterprise tables
        $counts = [
            'tenants' => Tenant::count(),
            'users' => User::count(),
            'employees' => Employee::withoutGlobalScopes()->count(),
            'attendances' => Attendance::withoutGlobalScopes()->count(),
            'payrolls' => Payroll::withoutGlobalScopes()->count(),
            'audit_logs' => AuditLog::withoutGlobalScopes()->count(),
        ];

        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connected' => $dbConnected,
            'database_version' => $dbVersion,
            'database_driver' => config('database.default'),
            'storage_writable' => $storageWritable,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'table_counts' => $counts,
            'server_time' => now()->toIso8601String(),
        ];
    }
}
