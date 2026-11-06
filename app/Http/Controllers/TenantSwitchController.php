<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class TenantSwitchController extends Controller
{
    public function switch(Request $request, int $tenantId): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->is_super_admin && !$user->tenants()->where('tenants.id', $tenantId)->exists()) {
            abort(403, 'Anda tidak terdaftar dalam entitas perusahaan yang dipilih.');
        }

        $tenant = Tenant::findOrFail($tenantId);

        $user->update(['active_tenant_id' => $tenant->id]);
        $request->session()->put('active_tenant_id', $tenant->id);

        return redirect()->route('company.dashboard')->with('success', "Berhasil beralih ke ruang kerja perusahaan: {$tenant->name}");
    }
}

