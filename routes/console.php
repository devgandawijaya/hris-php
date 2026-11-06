<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Tenant;

// Daily check for expiring subscriptions
Schedule::call(function () {
    Tenant::where('status', 'trial')
        ->whereNotNull('trial_ends_at')
        ->where('trial_ends_at', '<', now())
        ->update(['status' => 'expired']);
})->dailyAt('23:59');

