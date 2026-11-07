<?php

namespace App\Services\Attendance;

use App\Models\Employee;
use App\Models\FwaSchedule;
use App\Models\User;
use Carbon\Carbon;

class FwaService
{
    /**
     * Submit a flexible working arrangement schedule request.
     */
    public function requestFwa(Employee $employee, string $date, string $mode, ?string $reason = null): FwaSchedule
    {
        return FwaSchedule::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'date' => Carbon::parse($date)->toDateString(),
            ],
            [
                'company_id' => $employee->company_id,
                'work_mode' => in_array($mode, ['wfh', 'wfo']) ? $mode : 'wfo',
                'reason' => $reason,
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]
        );
    }

    /**
     * Approve or reject FWA schedule.
     */
    public function processApproval(FwaSchedule $schedule, User $approver, bool $approved): FwaSchedule
    {
        $schedule->status = $approved ? 'approved' : 'rejected';
        $schedule->approved_by = $approver->id;
        $schedule->approved_at = now();
        $schedule->save();

        return $schedule;
    }

    /**
     * Check if employee has approved WFH for a specific date.
     */
    public function isWfhOnDate(Employee $employee, string $date): bool
    {
        return FwaSchedule::where('employee_id', $employee->id)
            ->where('date', Carbon::parse($date)->toDateString())
            ->where('work_mode', 'wfh')
            ->where('status', 'approved')
            ->exists();
    }
}
