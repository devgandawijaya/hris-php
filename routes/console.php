<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Tenant;

// 1. Daily check for expiring tenant SaaS subscriptions
Schedule::call(function () {
    Tenant::where('status', 'trial')
        ->whereNotNull('trial_ends_at')
        ->where('trial_ends_at', '<', now())
        ->update(['status' => 'expired']);
})->dailyAt('23:59');

// 2. Daily check for expiring PKWT contracts (PP 35/2021)
Schedule::command('hris:check-contract-expirations')->dailyAt('01:00');

// 3. Daily check and auto-expire Surat Peringatan past 6 months (Pasal 161 UU 13/2003)
Schedule::command('hris:expire-warning-letters')->dailyAt('02:00');

// 4. Daily check for PKWTT probation completions approaching 3-month limit (Pasal 60 UU Ketenagakerjaan)
Schedule::command('hris:check-probation-expirations')->dailyAt('03:00');

// 5. Daily check for upcoming due loan installments for payroll deduction prep (PP 36/2021)
Schedule::command('hris:process-loan-installments')->dailyAt('04:00');
