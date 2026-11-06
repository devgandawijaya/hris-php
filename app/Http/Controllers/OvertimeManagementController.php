<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use App\Services\Compliance\OvertimeCalculator;
use App\Tenancy\TenantManager;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OvertimeManagementController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected OvertimeCalculator $overtimeCalculator
    ) {}

    public function index(Request $request): View
    {
        $query = OvertimeRequest::with(['employee.department', 'employee.position']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $overtimes = $query->latest('date')->paginate(15)->withQueryString();

        return view('company.overtime.index', compact('overtimes'));
    }

    public function approve(int $id): RedirectResponse
    {
        $ot = OvertimeRequest::with('employee')->findOrFail($id);

        // Calculate pay per PP 35/2021
        $baseWage = (float) ($ot->employee?->basic_salary ?? 0);
        $calc = $this->overtimeCalculator->calculate($baseWage, (float) $ot->hours, (bool) $ot->is_holiday);

        $ot->update([
            'status' => 'approved',
            'calculated_pay' => $calc['overtime_pay'],
        ]);

        return back()->with('success', "Permohonan lembur {$ot->employee?->full_name} ({$ot->hours} jam) berhasil disetujui. Estimasi upah lembur PP 35/2021: Rp " . number_format($calc['overtime_pay'], 0, ',', '.'));
    }

    public function reject(int $id): RedirectResponse
    {
        $ot = OvertimeRequest::findOrFail($id);
        $ot->update(['status' => 'rejected']);

        return back()->with('success', 'Permohonan lembur telah ditolak.');
    }
}
