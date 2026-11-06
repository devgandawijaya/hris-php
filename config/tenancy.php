<?php

return [
    'subdomain_enabled' => env('TENANCY_SUBDOMAIN_ENABLED', false),
    'main_domain' => env('APP_DOMAIN', 'localhost'),
    'header_name' => 'X-Tenant-ID',
    'session_key' => 'active_tenant_id',
    'default_trial_days' => 14,
];

