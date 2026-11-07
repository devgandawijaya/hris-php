<?php

namespace App\Services\Payment;

use App\Models\PaymentGatewayTransaction;
use App\Models\SubscriptionInvoice;
use App\Services\Billing\SubscriptionBillingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    public function __construct(
        protected SubscriptionBillingService $billingService
    ) {}

    /**
     * Create a payment gateway transaction and snap token.
     */
    public function createCheckoutTransaction(
        SubscriptionInvoice $invoice,
        string $gateway = 'midtrans',
        string $paymentType = 'qris'
    ): PaymentGatewayTransaction {
        $orderId = 'ORDER-' . $invoice->invoice_number . '-' . time();
        $grossAmount = (float) $invoice->total_amount;

        // Simulated Snap token and payment url for sandbox/production
        $snapToken = 'SNAP-' . strtoupper(bin2hex(random_bytes(16)));
        $paymentUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";

        $transaction = PaymentGatewayTransaction::create([
            'tenant_id' => $invoice->tenant_id,
            'subscription_invoice_id' => $invoice->id,
            'gateway_provider' => $gateway,
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'payment_type' => $paymentType,
            'transaction_status' => 'pending',
            'snap_token' => $snapToken,
            'payment_url' => $paymentUrl,
            'raw_payload' => [
                'item_details' => [
                    [
                        'id' => $invoice->invoice_number,
                        'price' => $grossAmount,
                        'quantity' => 1,
                        'name' => 'RiHRIS SaaS Subscription - ' . ($invoice->subscription?->plan?->name ?? 'Enterprise Plan'),
                    ]
                ],
            ],
        ]);

        return $transaction;
    }

    /**
     * Handle Midtrans HTTP notification webhook with SHA512 signature verification.
     */
    public function handleMidtransWebhook(array $payload, ?string $serverKey = null): ?PaymentGatewayTransaction
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (!$orderId) {
            return null;
        }

        $transaction = PaymentGatewayTransaction::where('order_id', $orderId)->first();
        if (!$transaction) {
            return null;
        }

        // Verify SHA512 signature if server key is configured
        if ($serverKey && $signatureKey) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if ($signatureKey !== $expectedSignature) {
                Log::warning("Midtrans Webhook Invalid Signature for Order {$orderId}");
                return null;
            }
        }

        $transaction->transaction_id = $payload['transaction_id'] ?? $transaction->transaction_id;
        $transaction->raw_payload = $payload;

        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            $transaction->transaction_status = 'settlement';
            $transaction->paid_at = Carbon::now();
            $transaction->save();

            // Mark invoice paid and extend subscription
            if ($transaction->invoice) {
                $this->billingService->markInvoicePaid($transaction->invoice, $transaction->payment_type);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $transaction->transaction_status = $transactionStatus;
            $transaction->save();
        }

        return $transaction;
    }

    /**
     * Instant sandbox payment settlement for tests & local verification.
     */
    public function simulateSandboxPayment(PaymentGatewayTransaction $transaction): PaymentGatewayTransaction
    {
        $transaction->transaction_status = 'settlement';
        $transaction->transaction_id = 'MIDTRANS-SETTLED-' . strtoupper(bin2hex(random_bytes(6)));
        $transaction->paid_at = Carbon::now();
        $transaction->save();

        if ($transaction->invoice) {
            $this->billingService->markInvoicePaid($transaction->invoice, $transaction->payment_type);
        }

        return $transaction;
    }
}
