<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Services\Billing\SubscriptionBillingService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected SubscriptionBillingService $billingService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();
        $usage = $this->billingService->getSubscriptionUsage($tenant);

        $plans = SubscriptionPlan::where('is_active', true)->get();

        $invoices = SubscriptionInvoice::where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(10);

        return view('company.billing.index', compact('usage', 'plans', 'invoices'));
    }

    public function changePlan(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $sub = $tenant->subscription;

        if (!$sub) {
            return back()->with('error', 'Langganan aktif tidak ditemukan.');
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $newPlan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $invoice = $this->billingService->changePlan($sub, $newPlan, $validated['billing_cycle']);

        return back()->with('success', "Paket berhasil dialihkan ke {$newPlan->name}. Tagihan diterbitkan dengan nomor {$invoice->invoice_number}.");
    }

    public function payInvoice(Request $request, SubscriptionInvoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:bank_transfer,virtual_account,credit_card,qris',
        ]);

        $this->billingService->markInvoicePaid($invoice, $validated['payment_method']);

        return back()->with('success', "Pembayaran tagihan {$invoice->invoice_number} berhasil dikonfirmasi. Layanan paket aktif.");
    }
}

