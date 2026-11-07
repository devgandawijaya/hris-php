<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\CompanyAsset;
use App\Models\AssetHandoverBast;
use App\Models\SystemAccessRevocationLog;
use App\Services\Offboarding\ExitAssetClearanceService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AssetHandoverController extends Controller
{
    public function __construct(
        protected ExitAssetClearanceService $assetClearanceService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $basts = AssetHandoverBast::with(['employee.department', 'items'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('handover_date')
            ->paginate(10);

        $revocationLogs = SystemAccessRevocationLog::with(['employee', 'revokedByUser'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->take(15)
            ->get();

        $employees = Employee::where('company_id', $company->id)->orderBy('first_name')->get();
        $availableAssets = CompanyAsset::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('company.asset_handover.index', compact('company', 'basts', 'revocationLogs', 'employees', 'availableAssets'));
    }

    public function storeBast(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'officer_name' => 'required|string|max:100',
            'officer_position' => 'required|string|max:100',
            'clearance_id' => 'nullable|exists:offboarding_clearances,id',
            'items' => 'required|array|min:1',
            'items.*.asset_name' => 'required|string|max:150',
            'items.*.company_asset_id' => 'nullable|exists:company_assets,id',
            'items.*.serial_number' => 'nullable|string|max:100',
            'items.*.condition_on_return' => 'required|in:good,minor_damage,severe_damage,lost',
            'items.*.compensation_amount' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        $bast = $this->assetClearanceService->createBast(
            $employee,
            $validated['items'],
            $validated['officer_name'],
            $validated['officer_position'],
            $validated['clearance_id'] ?? null
        );

        return redirect()->route('company.asset-handover.show', $bast->id)
            ->with('success', "Draft Berita Acara Serah Terima Aset ({$bast->bast_number}) berhasil dibuat.");
    }

    public function show(AssetHandoverBast $bast): View
    {
        $bast->load(['employee.department', 'company', 'items.companyAsset', 'accessRevocationLogs']);
        return view('company.asset_handover.show', compact('bast'));
    }

    public function signBast(Request $request, AssetHandoverBast $bast): RedirectResponse
    {
        $validated = $request->validate([
            'employee_signature' => 'required|string|min:4',
            'officer_signature' => 'required|string|min:4',
        ]);

        $this->assetClearanceService->signAndCompleteBast(
            $bast,
            $validated['employee_signature'],
            $validated['officer_signature']
        );

        return back()->with('success', "BAST {$bast->bast_number} resmi ditandatangani dan diselesaikan. Inventaris aset telah diperbarui dan pencabutan akses SSO telah dipicu otomatis.");
    }

    public function printBast(AssetHandoverBast $bast): View
    {
        $bast->load(['employee.department', 'company', 'items.companyAsset']);
        return view('company.asset_handover.print_bast', compact('bast'));
    }

    public function revokeAccess(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'system_name' => 'required|string|max:100',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        $this->assetClearanceService->revokeSystemAccess(
            $employee,
            $validated['system_name'],
            auth()->user()
        );

        return back()->with('success', "Pencabutan akses akun {$validated['system_name']} untuk {$employee->full_name} berhasil dicatat.");
    }
}
