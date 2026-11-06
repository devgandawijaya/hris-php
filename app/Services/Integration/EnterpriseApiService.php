<?php

namespace App\Services\Integration;

use App\Models\ApiKey;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EnterpriseApiService
{
    /**
     * Generate a new enterprise API key for external software integration.
     */
    public function generateApiKey(int $tenantId, string $name, array $permissions = ['*'], ?Carbon $expiresAt = null): array
    {
        $prefix = 'ri_live_';
        $random = Str::random(32);
        $plainKey = $prefix . $random;
        $hash = hash('sha256', $plainKey);

        $apiKey = ApiKey::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'key_prefix' => $prefix,
            'key_hash' => $hash,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return [
            'model' => $apiKey,
            'plain_key' => $plainKey,
        ];
    }

    /**
     * Validate incoming plain API key and record usage.
     */
    public function validateApiKey(string $plainKey): ?ApiKey
    {
        $hash = hash('sha256', $plainKey);

        $apiKey = ApiKey::where('key_hash', $hash)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            return null;
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return null;
        }

        $apiKey->update(['last_used_at' => Carbon::now()]);

        return $apiKey;
    }

    /**
     * Revoke an existing API key.
     */
    public function revokeApiKey(ApiKey $apiKey): void
    {
        $apiKey->update(['is_active' => false]);
    }
}

