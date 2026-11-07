<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayTransaction;
use App\Models\SubscriptionInvoice;
use App\Services\Payment\PaymentGatewayService;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected PaymentGatewayService $gatewayService
    ) {}

    public function checkout(Request $request, SubscriptionInvoice $invoice): View
    {
        $validated = $request->validate([
            'payment_type' => ['nullable', 'string', 'in:qris,bca_va,mandiri_va,bni_va,credit_card'],
        ]);

        $tenant = $this->tenantManager->getTenant();
        $company = \App\Models\Company::where('tenant_id', $tenant->id)->first() ?? \App\Models\Company::first();

        $paymentType = $validated['payment_type'] ?? 'qris';
        $transaction = $this->gatewayService->createCheckoutTransaction($invoice, 'midtrans', $paymentType);

        $plan = $invoice->subscription?->plan ?? null;
        $snapToken = $transaction->snap_token;
        $clientKey = config('services.midtrans.client_key', 'SB-Mid-client-demo123456');
        $isConfigured = !empty(config('services.midtrans.server_key'));

        return view('company.billing.checkout', compact('company', 'invoice', 'plan', 'snapToken', 'transaction', 'isConfigured', 'clientKey'));
    }

    public function simulateSuccess(Request $request, PaymentGatewayTransaction $transaction): RedirectResponse
    {
        $this->gatewayService->simulateSandboxPayment($transaction);

        return redirect()->route('company.billing.index')
            ->with('success', "Pembayaran simulasi berhasil diverifikasi! Paket langganan SaaS Anda telah aktif dan diperpanjang.");
    }

    public function midtransWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $serverKey = config('services.midtrans.server_key');

        $transaction = $this->gatewayService->handleMidtransWebhook($payload, $serverKey);

        if (!$transaction) {
            return response()->json(['status' => 'ignored'], 200);
        }

        return response()->json(['status' => 'success', 'order_id' => $transaction->order_id], 200);
    }
}
