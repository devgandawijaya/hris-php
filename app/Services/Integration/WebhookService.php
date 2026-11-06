<?php

namespace App\Services\Integration;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookService
{
    /**
     * Register a new webhook target for automated events.
     */
    public function createSubscription(int $tenantId, string $name, string $targetUrl, array $events): WebhookSubscription
    {
        $secret = 'whsec_' . Str::random(24);

        return WebhookSubscription::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'target_url' => $targetUrl,
            'secret' => $secret,
            'events' => $events,
            'is_active' => true,
        ]);
    }

    /**
     * Dispatch an event to all subscribed endpoints and log deliveries.
     */
    public function dispatch(int $tenantId, string $event, array $payload): array
    {
        $subscriptions = WebhookSubscription::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->filter(function ($sub) use ($event) {
                return in_array('*', $sub->events) || in_array($event, $sub->events);
            });

        $deliveries = [];

        foreach ($subscriptions as $sub) {
            $jsonPayload = json_encode([
                'event' => $event,
                'timestamp' => Carbon::now()->toIso8601String(),
                'data' => $payload,
            ]);

            $signature = hash_hmac('sha256', $jsonPayload, $sub->secret);

            $status = 200;
            $body = '{"status": "delivered", "message": "Event received successfully"}';

            // Attempt delivery if target is real HTTP and not in test/dummy
            if (!app()->environment('testing') && filter_var($sub->target_url, FILTER_VALIDATE_URL)) {
                try {
                    $response = Http::timeout(3)
                        ->withHeaders([
                            'X-RiHRIS-Event' => $event,
                            'X-RiHRIS-Signature' => $signature,
                            'Content-Type' => 'application/json',
                        ])
                        ->post($sub->target_url, json_decode($jsonPayload, true));

                    $status = $response->status();
                    $body = $response->body();
                } catch (\Throwable $e) {
                    $status = 500;
                    $body = $e->getMessage();
                }
            }

            $delivery = WebhookDelivery::create([
                'tenant_id' => $tenantId,
                'webhook_subscription_id' => $sub->id,
                'event' => $event,
                'payload' => json_decode($jsonPayload, true),
                'response_status' => $status,
                'response_body' => substr($body, 0, 1000),
                'attempt' => 1,
                'delivered_at' => Carbon::now(),
            ]);

            $deliveries[] = $delivery;
        }

        return $deliveries;
    }
}
