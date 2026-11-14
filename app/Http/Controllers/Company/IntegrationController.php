<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\WebhookSubscription;
use App\Models\WhatsappNotificationLog;
use App\Services\Integration\EnterpriseApiService;
use App\Services\Integration\OpenApiService;
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
        protected WhatsAppNotificationService $waService,
        protected OpenApiService $openApiService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();

        $apiKeys = ApiKey::where('tenant_id', $tenant->id)->latest()->get();
        $webhooks = WebhookSubscription::withCount('deliveries')->where('tenant_id', $tenant->id)->get();
        $waLogs = WhatsappNotificationLog::where('tenant_id', $tenant->id)->latest()->take(20)->get();
        $openApiSpec = $this->openApiService->getSpec();

        $availableScopes = [
            '*' => 'Akses Penuh Seluruh Endpoint (*)',
            'employees:read' => 'Baca Master Karyawan (GET /employees)',
            'employees:write' => 'Daftar / Ubah Karyawan (POST, PUT /employees)',
            'attendance:read' => 'Baca Log Presensi (GET /attendance)',
            'attendance:write' => 'Kirim Presensi Mesin Biometrik (POST /attendance, /bulk)',
            'departments:read' => 'Baca Data Departemen (GET /departments)',
            'leave:read' => 'Baca Status Cuti (GET /leave/requests, /types)',
            'leave:write' => 'Ajukan Permohonan Cuti (POST /leave/requests)',
            'payroll:read' => 'Baca Ringkasan Payroll untuk ERP (GET /payroll/summary)',
        ];

        $initialTab = $request->query('tab', 'api');

        return view('company.integrations.index', compact(
            'apiKeys',
            'webhooks',
            'waLogs',
            'openApiSpec',
            'availableScopes',
            'initialTab'
        ));
    }

    public function openapi(): View
    {
        $tenant = $this->tenantManager->getTenant();
        $apiKeys = ApiKey::where('tenant_id', $tenant->id)->where('is_active', true)->latest()->get();
        $openApiSpec = $this->openApiService->getSpec();

        return view('company.integrations.openapi', compact('tenant', 'apiKeys', 'openApiSpec'));
    }

    public function storeApiKey(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'permissions' => 'nullable|array',
        ]);

        $permissions = $validated['permissions'] ?? ['*'];
        if (empty($permissions)) {
            $permissions = ['*'];
        }

        $res = $this->apiService->generateApiKey(
            $tenant->id,
            $validated['name'],
            $permissions
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
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        $this->waService->sendRaw(
            $tenant->id,
            $validated['phone'],
            $validated['message'],
            'manual_test'
        );

        return back()->with('success', "Pesan simulasi WhatsApp berhasil dikirim ke {$validated['phone']}.");
    }
}
