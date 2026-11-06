<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CompanyManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tenant::with(['companies', 'activeSubscription.plan'])
            ->withCount(['companies', 'users']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tenants = $query->latest()->paginate(10)->withQueryString();

        return view('super-admin.companies.index', compact('tenants'));
    }

    public function show(int $id): View
    {
        $tenant = Tenant::with(['companies.settings', 'users', 'activeSubscription.plan'])
            ->findOrFail($id);

        return view('super-admin.companies.show', compact('tenant'));
    }

    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $oldStatus = $tenant->status;
        $newStatus = $oldStatus === 'active' ? 'suspended' : 'active';

        $tenant->update(['status' => $newStatus]);

        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => Auth::id(),
            'action' => 'tenant.status_change',
            'auditable_type' => Tenant::class,
            'auditable_id' => $tenant->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Status perusahaan {$tenant->name} berhasil diubah menjadi: {$newStatus}");
    }
}

