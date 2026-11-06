<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\WebhookSubscription;
use App\Models\WhatsappNotificationLog;
use App\Services\Integration\EnterpriseApiService;
use App\Services\Integration\WebhookService;
use App\Services\Notification\WhatsAppNotificationService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected EnterpriseApiService $apiService,
        protected WebhookService $webhookService,
        protected WhatsAppNotificationService $waService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();

        $apiKeys = ApiKey::where('tenant_id', $tenant->id)->latest()->get();
        $webhooks = WebhookSubscription::withCount('deliveries')->where('tenant_id', $tenant->id)->get();
        $waLogs = WhatsappNotificationLog::where('tenant_id', $tenant->id)->latest()->take(20)->get();

        return view('company.integrations.index', compact('apiKeys', 'webhooks', 'waLogs'));
    }

    public function storeApiKey(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'permissions' => 'nullable|array',
        ]);

        $res = $this->apiService->generateApiKey(
            $tenant->id,
            $validated['name'],
            $validated['permissions'] ?? ['*']
        );

        return back()->with('api_key_created', [
            'name' => $validated['name'],
            'plain_key' => $res['plain_key'],
        ])->with('success', 'API Key berhasil dibuat. Pastikan untuk menyalin secret key sekarang karena tidak akan ditampilkan lagi.');
    }

    public function revokeApiKey(ApiKey $apiKey): RedirectResponse
    {
        $this->apiService->revokeApiKey($apiKey);

        return back()->with('success', 'API Key berhasil dinonaktifkan.');
    }

    public function storeWebhook(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'target_url' => 'required|url|max:255',
            'events' => 'required|array|min:1',
        ]);

        $this->webhookService->createSubscription(
            $tenant->id,
            $validated['name'],
            $validated['target_url'],
            $validated['events']
        );

        return back()->with('success', 'Webhook subscription berhasil didaftarkan.');
    }

    public function testWebhook(WebhookSubscription $webhook): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $this->webhookService->dispatch($tenant->id, 'ping.test', [
            'message' => 'Uji coba pengiriman payload webhook dari platform RiHRIS Enterprise',
            'tenant' => $tenant->name,
            'timestamp' => now()->toIso8601String(),
        ]);

        return back()->with('success', "Event uji coba (ping.test) berhasil dikirim ke {$webhook->target_url}.");
    }

    public function testWhatsApp(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:500',
        ]);

        WhatsappNotificationLog::create([
            'tenant_id' => $tenant->id,
            'phone_number' => $validated['phone'],
            'template_name' => 'manual_test',
            'message_content' => $validated['message'],
            'status' => 'sent',
        ]);

        return back()->with('success', "Pesan notifikasi WhatsApp simulasi berhasil dikirim ke {$validated['phone']}.");
    }
}
