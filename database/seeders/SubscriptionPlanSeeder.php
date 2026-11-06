<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'free-trial',
                'price_monthly' => 0.00,
                'price_yearly' => 0.00,
                'max_employees' => 20,
                'max_admins' => 2,
                'storage_limit_mb' => 512,
                'features' => [
                    'core_hr' => true,
                    'gps_attendance' => true,
                    'basic_leave' => true,
                    'payslip' => false,
                    'pph21_ter' => false,
                    'bpjs_calc' => false,
                    'custom_workflow' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price_monthly' => 499000.00,
                'price_yearly' => 4990000.00,
                'max_employees' => 50,
                'max_admins' => 3,
                'storage_limit_mb' => 2048,
                'features' => [
                    'core_hr' => true,
                    'gps_attendance' => true,
                    'basic_leave' => true,
                    'payslip' => true,
                    'pph21_ter' => true,
                    'bpjs_calc' => true,
                    'custom_workflow' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'price_monthly' => 1499000.00,
                'price_yearly' => 14990000.00,
                'max_employees' => 250,
                'max_admins' => 10,
                'storage_limit_mb' => 10240,
                'features' => [
                    'core_hr' => true,
                    'gps_attendance' => true,
                    'basic_leave' => true,
                    'payslip' => true,
                    'pph21_ter' => true,
                    'bpjs_calc' => true,
                    'overtime_pp35' => true,
                    'pkwt_compensation' => true,
                    'custom_workflow' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price_monthly' => 3999000.00,
                'price_yearly' => 39990000.00,
                'max_employees' => 99999,
                'max_admins' => 99999,
                'storage_limit_mb' => 102400,
                'features' => [
                    'core_hr' => true,
                    'gps_attendance' => true,
                    'basic_leave' => true,
                    'payslip' => true,
                    'pph21_ter' => true,
                    'bpjs_calc' => true,
                    'overtime_pp35' => true,
                    'pkwt_compensation' => true,
                    'custom_workflow' => true,
                    'api_access' => true,
                    'dedicated_account_manager' => true,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}

