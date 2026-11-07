<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::withCount('subscriptions')->get();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'max_employees' => ['required', 'integer', 'min:1'],
            'storage_limit_mb' => ['required', 'integer', 'min:100'],
            'has_api_access' => ['nullable', 'boolean'],
            'has_whatsapp' => ['nullable', 'boolean'],
            'has_custom_workflows' => ['nullable', 'boolean'],
            'has_bank_disbursal' => ['nullable', 'boolean'],
            'has_ebupot_export' => ['nullable', 'boolean'],
            'has_digital_contracts' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['has_api_access'] = $request->boolean('has_api_access');
        $validated['has_whatsapp'] = $request->boolean('has_whatsapp');
        $validated['has_custom_workflows'] = $request->boolean('has_custom_workflows');
        $validated['has_bank_disbursal'] = $request->boolean('has_bank_disbursal');
        $validated['has_ebupot_export'] = $request->boolean('has_ebupot_export');
        $validated['has_digital_contracts'] = $request->boolean('has_digital_contracts');
        $validated['is_active'] = true;

        SubscriptionPlan::create($validated);

        return back()->with('success', "Paket langganan {$validated['name']} berhasil dibuat.");
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'max_employees' => ['required', 'integer', 'min:1'],
            'storage_limit_mb' => ['required', 'integer', 'min:100'],
            'has_api_access' => ['nullable', 'boolean'],
            'has_whatsapp' => ['nullable', 'boolean'],
            'has_custom_workflows' => ['nullable', 'boolean'],
            'has_bank_disbursal' => ['nullable', 'boolean'],
            'has_ebupot_export' => ['nullable', 'boolean'],
            'has_digital_contracts' => ['nullable', 'boolean'],
        ]);

        $validated['has_api_access'] = $request->boolean('has_api_access');
        $validated['has_whatsapp'] = $request->boolean('has_whatsapp');
        $validated['has_custom_workflows'] = $request->boolean('has_custom_workflows');
        $validated['has_bank_disbursal'] = $request->boolean('has_bank_disbursal');
        $validated['has_ebupot_export'] = $request->boolean('has_ebupot_export');
        $validated['has_digital_contracts'] = $request->boolean('has_digital_contracts');

        $plan->update($validated);

        return back()->with('success', "Paket langganan {$plan->name} berhasil diperbarui.");
    }

    public function toggle(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Paket {$plan->name} berhasil {$status}.");
    }
}
