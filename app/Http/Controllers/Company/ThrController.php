<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ThrRecord;
use App\Services\Compliance\ThrCalculator;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThrController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected ThrCalculator $thrCalculator
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $selectedYear = (int) $request->input('year', date('Y'));

        $records = ThrRecord::with(['employee.department', 'employee.position'])
            ->where('tenant_id', $tenant->id)
            ->where('year', $selectedYear)
            ->get();

        $stats = [
            'total_recipients' => $records->count(),
            'total_thr_amount' => $records->sum('thr_amount'),
            'total_paid' => $records->where('status', 'paid')->sum('thr_amount'),
        ];

        return view('company.payroll.thr', compact('records', 'selectedYear', 'stats'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
            'payment_date' => 'required|date',
        ]);

        $result = $this->thrCalculator->generateTenantThr(
            $tenant->id,
            (int) $validated['year'],
            $validated['payment_date']
        );

        return back()->with('success', "Kalkulasi THR Tahun {$result['year']} berhasil diproses untuk {$result['total_eligible_employees']} karyawan berhak sesuai Permenaker No. 6/2016. Total anggaran: Rp " . number_format($result['total_thr_amount'], 2, ',', '.') . ".");
    }

    public function markPaid(ThrRecord $thrRecord): RedirectResponse
    {
        $thrRecord->update(['status' => 'paid']);
        return back()->with('success', 'Status pembayaran THR berhasil diperbarui menjadi Lunas (Paid).');
    }
}
