<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchQrKiosk;
use App\Services\Attendance\QrAttendanceService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrKioskController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected QrAttendanceService $qrService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();

        $branches = Branch::where('tenant_id', $tenant->id)->get();

        $kiosks = BranchQrKiosk::with('branch')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        return view('company.qr.index', compact('branches', 'kiosks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'kiosk_name' => 'required|string|max:100',
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);

        $kiosk = $this->qrService->generateOrRefreshKiosk($branch, $validated['kiosk_name']);

        return back()->with('success', "Kios presensi QR '{$kiosk->kiosk_name}' untuk cabang {$branch->name} berhasil diaktifkan.");
    }

    public function refresh(BranchQrKiosk $kiosk): RedirectResponse
    {
        $this->qrService->generateOrRefreshKiosk($kiosk->branch, $kiosk->kiosk_name);

        return back()->with('success', "Token keamanan QR Kios '{$kiosk->kiosk_name}' berhasil diperbarui.");
    }

    public function display(BranchQrKiosk $kiosk): View
    {
        $kiosk->load('branch');
        return view('company.qr.display', compact('kiosk'));
    }
}
