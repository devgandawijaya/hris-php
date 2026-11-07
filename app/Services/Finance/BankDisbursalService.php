<?php

namespace App\Services\Finance;

use App\Models\BankDisbursalBatch;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Support\Str;

class BankDisbursalService
{
    /**
     * Validate employee bank account completeness for a payroll period.
     */
    public function validateEmployeeBankAccounts(PayrollPeriod $period): array
    {
        $payrolls = Payroll::with('employee')
            ->where('payroll_period_id', $period->id)
            ->get();

        $valid = [];
        $invalid = [];

        foreach ($payrolls as $p) {
            $emp = $p->employee;
            $hasBank = !empty($emp->bank_name);
            $hasNumber = !empty($emp->bank_account_number);
            $hasHolder = !empty($emp->bank_account_holder);

            if ($hasBank && $hasNumber && $hasHolder && $p->take_home_pay > 0) {
                $valid[] = $p;
            } else {
                $missingFields = [];
                if (!$hasBank) $missingFields[] = 'Nama Bank';
                if (!$hasNumber) $missingFields[] = 'Nomor Rekening';
                if (!$hasHolder) $missingFields[] = 'Nama Pemilik Rekening';
                if ($p->take_home_pay <= 0) $missingFields[] = 'THP <= 0';

                $invalid[] = [
                    'employee_id' => $emp->id,
                    'employee_number' => $emp->employee_number,
                    'name' => $emp->full_name,
                    'missing' => implode(', ', $missingFields),
                    'take_home_pay' => $p->take_home_pay,
                ];
            }
        }

        return [
            'total_employees' => $payrolls->count(),
            'valid_count' => count($valid),
            'invalid_count' => count($invalid),
            'valid_records' => $valid,
            'invalid_records' => $invalid,
            'total_disbursal_amount' => collect($valid)->sum('take_home_pay'),
        ];
    }

    /**
     * Generate a new bank disbursal batch.
     */
    public function generateDisbursalBatch(PayrollPeriod $period, string $bankTarget, ?int $userId = null): BankDisbursalBatch
    {
        $validation = $this->validateEmployeeBankAccounts($period);

        $batchNumber = 'DISB-' . strtoupper($bankTarget) . '-' . date('Ymd') . '-' . Str::upper(Str::random(5));

        return BankDisbursalBatch::create([
            'tenant_id' => $period->tenant_id,
            'payroll_period_id' => $period->id,
            'batch_number' => $batchNumber,
            'bank_target' => strtolower($bankTarget),
            'total_records' => $validation['valid_count'],
            'total_amount' => $validation['total_disbursal_amount'],
            'file_format' => in_array(strtolower($bankTarget), ['bca']) ? 'csv' : 'csv',
            'status' => 'generated',
            'created_by_user_id' => $userId,
        ]);
    }

    /**
     * Export in BCA KlikBCA Payroll format (CSV format standard).
     * Header, Beneficiary Account, Beneficiary Name, Transfer Amount, Remark, Transfer Date
     */
    public function exportBcaKlikBca(PayrollPeriod $period): string
    {
        $payrolls = Payroll::with('employee')
            ->where('payroll_period_id', $period->id)
            ->where('take_home_pay', '>', 0)
            ->get();

        $output = fopen('php://temp', 'r+');

        // KlikBCA Header
        fputcsv($output, [
            'REK_TUJUAN',
            'NAMA_PENERIMA',
            'NOMINAL',
            'MATA_UANG',
            'BERITA',
            'KODE_TRANSAKSI',
            'EMAIL_PENERIMA'
        ]);

        foreach ($payrolls as $p) {
            $emp = $p->employee;
            if (empty($emp->bank_account_number)) continue;

            fputcsv($output, [
                preg_replace('/[^0-9]/', '', $emp->bank_account_number),
                strtoupper(substr($emp->bank_account_holder ?: $emp->full_name, 0, 30)),
                number_format($p->take_home_pay, 2, '.', ''),
                'IDR',
                "GAJI " . strtoupper(substr($period->name, 0, 15)),
                'SALARY',
                $emp->email ?? ''
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export in Mandiri Cash Management (MCM) standard format.
     */
    public function exportMandiriMcm(PayrollPeriod $period): string
    {
        $payrolls = Payroll::with('employee')
            ->where('payroll_period_id', $period->id)
            ->where('take_home_pay', '>', 0)
            ->get();

        $output = fopen('php://temp', 'r+');

        // MCM 2.0 Header
        fputcsv($output, [
            'BENEFICIARY_ACCOUNT',
            'BENEFICIARY_NAME',
            'BENEFICIARY_BANK',
            'AMOUNT',
            'TRANSACTION_REMARK_1',
            'TRANSACTION_REMARK_2',
            'BENEFICIARY_EMAIL'
        ]);

        foreach ($payrolls as $p) {
            $emp = $p->employee;
            if (empty($emp->bank_account_number)) continue;

            fputcsv($output, [
                preg_replace('/[^0-9]/', '', $emp->bank_account_number),
                strtoupper(substr($emp->bank_account_holder ?: $emp->full_name, 0, 40)),
                strtoupper($emp->bank_name ?: 'MANDIRI'),
                number_format($p->take_home_pay, 2, '.', ''),
                "PAYROLL " . strtoupper(substr($period->name, 0, 15)),
                $emp->employee_number,
                $emp->email ?? ''
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export in BNI Direct Mass Payment standard format.
     */
    public function exportBniDirect(PayrollPeriod $period): string
    {
        $payrolls = Payroll::with('employee')
            ->where('payroll_period_id', $period->id)
            ->where('take_home_pay', '>', 0)
            ->get();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'CREDIT_ACCOUNT_NO',
            'BENEFICIARY_NAME',
            'TRANSFER_AMOUNT',
            'BENEFICIARY_BANK_NAME',
            'PAYMENT_DETAILS',
            'REMARKS'
        ]);

        foreach ($payrolls as $p) {
            $emp = $p->employee;
            if (empty($emp->bank_account_number)) continue;

            fputcsv($output, [
                preg_replace('/[^0-9]/', '', $emp->bank_account_number),
                strtoupper(substr($emp->bank_account_holder ?: $emp->full_name, 0, 35)),
                number_format($p->take_home_pay, 2, '.', ''),
                strtoupper($emp->bank_name ?: 'BNI'),
                "SALARY " . strtoupper(substr($period->name, 0, 15)),
                $emp->employee_number
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export in BRI Corporate Cash Management (CMS) format.
     */
    public function exportBriCms(PayrollPeriod $period): string
    {
        $payrolls = Payroll::with('employee')
            ->where('payroll_period_id', $period->id)
            ->where('take_home_pay', '>', 0)
            ->get();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'NOMOR_REKENING_TUJUAN',
            'NAMA_PENERIMA',
            'NOMINAL_TRANSFER',
            'KODE_BANK',
            'KETERANGAN'
        ]);

        foreach ($payrolls as $p) {
            $emp = $p->employee;
            if (empty($emp->bank_account_number)) continue;

            fputcsv($output, [
                preg_replace('/[^0-9]/', '', $emp->bank_account_number),
                strtoupper(substr($emp->bank_account_holder ?: $emp->full_name, 0, 35)),
                number_format($p->take_home_pay, 2, '.', ''),
                strtoupper($emp->bank_name ?: 'BRI'),
                "GAJI " . strtoupper(substr($period->name, 0, 15))
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
