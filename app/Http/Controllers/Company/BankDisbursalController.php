<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BankDisbursalBatch;
use App\Models\PayrollPeriod;
use App\Services\Finance\BankDisbursalService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BankDisbursalController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected BankDisbursalService $disbursalService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();

        $periods = PayrollPeriod::where('tenant_id', $tenant->id)
            ->whereIn('status', ['approved', 'paid'])
            ->latest('start_date')
            ->get();

        $batches = BankDisbursalBatch::with('payrollPeriod', 'createdByUser')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(10);

        // Validation summary for latest active period if available
        $selectedPeriod = null;
        $validation = null;
        if ($periods->isNotEmpty()) {
            $selectedPeriod = $periods->first();
            $validation = $this->disbursalService->validateEmployeeBankAccounts($selectedPeriod);
        }

        return view('company.disbursal.index', compact('periods', 'batches', 'selectedPeriod', 'validation'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'bank_target' => 'required|in:bca,mandiri,bni,bri',
        ]);

        $period = PayrollPeriod::findOrFail($validated['payroll_period_id']);

        $batch = $this->disbursalService->generateDisbursalBatch(
            $period,
            $validated['bank_target'],
            auth()->id()
        );

        return back()->with('success', "Batch transfer perbankan {$batch->batch_number} berhasil dibuat ({$batch->total_records} rekening, total Rp " . number_format($batch->total_amount, 2, ',', '.') . ").");
    }

    public function download(BankDisbursalBatch $batch): StreamedResponse
    {
        $period = $batch->payrollPeriod;
        $bank = strtolower($batch->bank_target);

        $content = match ($bank) {
            'bca' => $this->disbursalService->exportBcaKlikBca($period),
            'mandiri' => $this->disbursalService->exportMandiriMcm($period),
            'bni' => $this->disbursalService->exportBniDirect($period),
            'bri' => $this->disbursalService->exportBriCms($period),
            default => $this->disbursalService->exportBcaKlikBca($period),
        };

        $filename = "payroll_disbursal_{$bank}_{$batch->batch_number}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($content) {
            echo $content;
        }, 200, $headers);
    }
}
