<?php

namespace App\Http\Middleware;

use App\Services\Integration\EnterpriseApiService;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEnterpriseApiKey
{
    public function __construct(
        protected EnterpriseApiService $apiService,
        protected TenantManager $tenantManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $headerKey = $request->header('X-API-KEY');
        if (!$headerKey && $request->bearerToken()) {
            $headerKey = $request->bearerToken();
        }

        if (!$headerKey) {
            return response()->json([
                'success' => false,
                'message' => 'Missing X-API-KEY header or Bearer token.',
            ], 401);
        }

        $apiKey = $this->apiService->validateApiKey($headerKey);
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API Key.',
            ], 401);
        }

        // Set tenant context for this API request
        $this->tenantManager->setCurrentTenant($apiKey->tenant);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}

