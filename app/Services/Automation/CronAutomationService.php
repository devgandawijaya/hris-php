<?php

namespace App\Services\Automation;

use App\Models\CronExecutionLog;
use App\Models\Employee;
use App\Models\EmployeeWarning;
use App\Models\LoanInstallment;
use App\Models\Tenant;
use App\Services\Notification\AppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CronAutomationService
{
    public function __construct(
        protected AppNotificationService $notificationService
    ) {}

    /**
     * 1. Check PKWT contracts expiring within 30 days (PP 35/2021 & Pasal 56-59 UU Ketenagakerjaan)
     */
    public function checkContractExpirations(): array
    {
        $now = Carbon::now();
        $targetDate = $now->copy()->addDays(30);

        // Fetch employees with contract ending within 30 days
        $expiringContracts = Employee::where('employment_status', 'contract')
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$now->toDateString(), $targetDate->toDateString()])
            ->with(['company', 'department', 'position'])
            ->get();

        $processed = 0;
        foreach ($expiringContracts as $emp) {
            $daysLeft = $now->diffInDays($emp->end_date, false);
            $daysLeftText = $daysLeft >= 0 ? "{$daysLeft} hari lagi" : "telah berakhir";

            $this->notificationService->sendToTenant(
                $emp->tenant_id,
                "Peringatan Kontrak PKWT: {$emp->full_name}",
                "Kontrak kerja PKWT karyawan {$emp->full_name} ({$emp->employee_code}) dari divisi {$emp->department?->name} akan berakhir pada {$emp->end_date->format('d/m/Y')} ({$daysLeftText}). Harap siapkan kompensasi PKWT (PP 35/2021) atau perpanjangan kontrak.",
                'warning',
                route('company.contracts.index')
            );
            $processed++;
        }

        $log = CronExecutionLog::create([
            'command_name' => 'hris:check-contract-expirations',
            'status' => 'success',
            'items_processed' => $processed,
            'summary' => "Ditemukan {$processed} kontrak PKWT yang akan berakhir dalam 30 hari ke depan.",
            'executed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'items_processed' => $processed,
            'summary' => $log->summary,
        ];
    }

    /**
     * 2. Automatically expire disciplinary warning letters older than 6 months (Pasal 161 UU 13/2003)
     */
    public function expireWarningLetters(): array
    {
        $today = Carbon::today()->toDateString();

        $expiredWarnings = EmployeeWarning::where('status', 'active')
            ->where('valid_until', '<', $today)
            ->get();

        $count = $expiredWarnings->count();

        if ($count > 0) {
            EmployeeWarning::where('status', 'active')
                ->where('valid_until', '<', $today)
                ->update(['status' => 'expired']);

            foreach ($expiredWarnings as $w) {
                $this->notificationService->sendToTenant(
                    $w->tenant_id,
                    "Masa Berlaku SP Berakhir: {$w->sp_number}",
                    "Surat Peringatan {$w->sp_number} atas nama karyawan bersangkutan telah habis masa berlakunya (6 bulan per Pasal 161 UU Ketenagakerjaan) dan telah otomatis berstatus expired.",
                    'info',
                    route('company.disciplinary.index')
                );
            }
        }

        $log = CronExecutionLog::create([
            'command_name' => 'hris:expire-warning-letters',
            'status' => 'success',
            'items_processed' => $count,
            'summary' => "Berhasil memperbarui {$count} Surat Peringatan (SP) yang telah melewati masa berlaku 6 bulan menjadi expired.",
            'executed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'items_processed' => $count,
            'summary' => $log->summary,
        ];
    }

    /**
     * 3. Check PKWTT probation approaching statutory 3-month limit (Pasal 60 UU Ketenagakerjaan)
     */
    public function checkProbationExpirations(): array
    {
        $today = Carbon::today();
        // Probation in Indonesia is max 3 months (~90 days). Notify if join_date is between 75 and 90 days ago.
        $rangeStart = $today->copy()->subDays(90);
        $rangeEnd = $today->copy()->subDays(75);

        $probationEmployees = Employee::where('employment_status', 'permanent')
            ->where('status', 'active')
            ->whereBetween('join_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->with(['department', 'position'])
            ->get();

        $processed = 0;
        foreach ($probationEmployees as $emp) {
            $daysEmployed = $emp->join_date->diffInDays($today);
            $daysRemaining = max(0, 90 - $daysEmployed);

            $this->notificationService->sendToTenant(
                $emp->tenant_id,
                "Evaluasi Masa Percobaan (Probasi): {$emp->full_name}",
                "Karyawan {$emp->full_name} ({$emp->position?->title}) akan menyelesaikan masa percobaan 3 bulan dalam {$daysRemaining} hari. Segera lakukan review kinerja dan terbitkan SK Pengangkatan Karyawan Tetap.",
                'info',
                route('company.employees.show', $emp->id)
            );
            $processed++;
        }

        $log = CronExecutionLog::create([
            'command_name' => 'hris:check-probation-expirations',
            'status' => 'success',
            'items_processed' => $processed,
            'summary' => "Ditemukan {$processed} karyawan PKWTT dalam rentang akhir masa percobaan (probasi) 3 bulan.",
            'executed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'items_processed' => $processed,
            'summary' => $log->summary,
        ];
    }

    /**
     * 4. Prepare and flag due loan installments for payroll deduction batch
     */
    public function processLoanInstallments(): array
    {
        $today = Carbon::today();
        $dueEnd = $today->copy()->addDays(7);

        // Find installments due in next 7 days that are pending
        $installments = LoanInstallment::where('status', 'pending')
            ->whereBetween('due_date', [$today->toDateString(), $dueEnd->toDateString()])
            ->with('loan.employee')
            ->get();

        $count = $installments->count();

        $log = CronExecutionLog::create([
            'command_name' => 'hris:process-loan-installments',
            'status' => 'success',
            'items_processed' => $count,
            'summary' => "Ditemukan {$count} angsuran pinjaman/kasbon yang jatuh tempo dalam 7 hari dan siap dipotong pada siklus payroll.",
            'executed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'items_processed' => $count,
            'summary' => $log->summary,
        ];
    }
}
