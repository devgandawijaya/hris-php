<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollProcessorService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected PayrollProcessorService $payrollProcessor
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');

        $query = PayrollPeriod::query()
            ->where('tenant_id', $tenant->id)
            ->withCount('payrolls')
            ->latest('start_date');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $periods = $query->paginate(10);

        // Summary statistics
        $totalPeriods = PayrollPeriod::where('tenant_id', $tenant->id)->count();
        $totalPaidOut = Payroll::where('tenant_id', $tenant->id)->where('status', 'paid')->sum('take_home_pay');
        $activeRun = PayrollPeriod::where('tenant_id', $tenant->id)->whereIn('status', ['draft', 'processed'])->first();

        return view('company.payroll.index', compact('periods', 'totalPeriods', 'totalPaidOut', 'activeRun'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'payment_date' => 'required|date|after_or_equal:start_date',
        ]);

        $period = PayrollPeriod::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'payment_date' => $validated['payment_date'],
            'status' => 'draft',
        ]);

        return redirect()->route('company.payroll.show', $period->id)
            ->with('success', "Periode payroll '{$period->name}' berhasil dibuat. Silakan klik 'Jalankan Kalkulasi Payroll' untuk memproses.");
    }

    public function show(PayrollPeriod $payroll): View
    {
        $period = $payroll; // Route parameter binding
        $tenant = $this->tenantManager->getTenant();

        $payrolls = Payroll::with(['employee.department', 'employee.position'])
            ->where('tenant_id', $tenant->id)
            ->where('payroll_period_id', $period->id)
            ->get();

        $metrics = [
            'total_employees' => $payrolls->count(),
            'total_gross' => $payrolls->sum('gross_salary'),
            'total_allowances' => $payrolls->sum('allowances_total'),
            'total_overtime' => $payrolls->sum('overtime_pay'),
            'total_reimbursements' => $payrolls->sum('reimbursement_pay'),
            'total_bpjs_tk_employee' => $payrolls->sum('bpjs_tk_employee'),
            'total_bpjs_tk_employer' => $payrolls->sum('bpjs_tk_employer'),
            'total_bpjs_kes_employee' => $payrolls->sum('bpjs_kes_employee'),
            'total_bpjs_kes_employer' => $payrolls->sum('bpjs_kes_employer'),
            'total_tax' => $payrolls->sum('pph21_tax'),
            'total_thp' => $payrolls->sum('take_home_pay'),
        ];

        return view('company.payroll.show', compact('period', 'payrolls', 'metrics'));
    }

    public function process(PayrollPeriod $payroll): RedirectResponse
    {
        $period = $payroll;
        $result = $this->payrollProcessor->processPeriod($period);

        return back()->with('success', "Kalkulasi payroll berhasil diproses untuk {$result['processed_employees']} karyawan. Total THP: Rp " . number_format($result['total_net'], 2, ',', '.') . ".");
    }

    public function approve(PayrollPeriod $payroll): RedirectResponse
    {
        $period = $payroll;
        $this->payrollProcessor->approvePeriod($period);

        return back()->with('success', "Periode payroll '{$period->name}' telah disetujui (Approved) dan siap dibayarkan.");
    }

    public function pay(PayrollPeriod $payroll): RedirectResponse
    {
        $period = $payroll;
        $this->payrollProcessor->markAsPaid($period);

        return back()->with('success', "Pembayaran gaji periode '{$period->name}' telah selesai dicairkan (Paid). Seluruh slip gaji diterbitkan ke karyawan.");
    }

    public function export(PayrollPeriod $payroll): StreamedResponse
    {
        $period = $payroll;
        $tenant = $this->tenantManager->getTenant();

        $payrolls = Payroll::with(['employee.department', 'employee.position'])
            ->where('tenant_id', $tenant->id)
            ->where('payroll_period_id', $period->id)
            ->get();

        $csvFileName = 'payroll_' . str_replace(' ', '_', strtolower($period->name)) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$csvFileName}\"",
        ];

        $callback = function () use ($payrolls, $period) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID Karyawan',
                'Nama Karyawan',
                'Departemen',
                'Jabatan',
                'Status PTKP',
                'Gaji Pokok',
                'Tunjangan',
                'Lembur',
                'Reimbursement',
                'Gaji Kotor (Gross)',
                'BPJS TK Karyawan',
                'BPJS Kes Karyawan',
                'PPh 21 TER',
                'Take Home Pay (THP)',
                'Bank',
                'Nomor Rekening',
                'Nama Rekening',
            ]);

            foreach ($payrolls as $row) {
                fputcsv($handle, [
                    $row->employee->employee_number,
                    $row->employee->full_name,
                    $row->employee->department?->name ?? '-',
                    $row->employee->position?->title ?? '-',
                    $row->employee->tax_ptkp_status ?? 'TK/0',
                    $row->basic_salary,
                    $row->allowances_total,
                    $row->overtime_pay,
                    $row->reimbursement_pay,
                    $row->gross_salary,
                    $row->bpjs_tk_employee,
                    $row->bpjs_kes_employee,
                    $row->pph21_tax,
                    $row->take_home_pay,
                    $row->employee->bank_name ?? '-',
                    $row->employee->bank_account_number ?? '-',
                    $row->employee->bank_account_holder ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

