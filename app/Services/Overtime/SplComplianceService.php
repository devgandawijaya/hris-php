<?php

namespace App\Services\Overtime;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Models\OvertimeOrder;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class SplComplianceService
{
    public const MAX_DAILY_WORKDAY_HOURS = 4.0;
    public const MAX_WEEKLY_WORKDAY_HOURS = 18.0;

    /**
     * Create an official Overtime Order (SPL) with PP 35/2021 regulatory checks.
     */
    public function createOrder(Company $company, Employee $employee, array $data): OvertimeOrder
    {
        $overtimeDate = Carbon::parse($data['overtime_date']);
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        // Calculate planned duration in hours
        $diffMinutes = $startTime->diffInMinutes($endTime);
        $plannedHours = round($diffMinutes / 60, 2);
        if ($plannedHours <= 0) {
            $plannedHours = (float) ($data['planned_duration_hours'] ?? 1.0);
        }

        $isDayOff = (bool) ($data['is_day_off'] ?? false);

        // PP 35/2021 Pasal 26 Ayat (1): Lembur hari kerja maksimal 4 jam / hari
        if (!$isDayOff && $plannedHours > self::MAX_DAILY_WORKDAY_HOURS) {
            throw ValidationException::withMessages([
                'planned_duration_hours' => [
                    "Durasi lembur hari kerja ({$plannedHours} jam) melanggar batas maksimal 4 (empat) jam per hari sesuai Pasal 26 ayat 1 PP No. 35 Tahun 2021."
                ]
            ]);
        }

        // PP 35/2021 Pasal 26 Ayat (1): Akumulasi lembur hari kerja maksimal 18 jam / minggu
        if (!$isDayOff) {
            $weekStart = $overtimeDate->copy()->startOfWeek()->toDateString();
            $weekEnd = $overtimeDate->copy()->endOfWeek()->toDateString();

            $existingWeeklyHours = (float) OvertimeOrder::where('employee_id', $employee->id)
                ->where('is_day_off', false)
                ->whereBetween('overtime_date', [$weekStart, $weekEnd])
                ->whereIn('status', ['pending_approval', 'approved', 'completed_verified'])
                ->sum('planned_duration_hours');

            if (($existingWeeklyHours + $plannedHours) > self::MAX_WEEKLY_WORKDAY_HOURS) {
                $totalProjected = $existingWeeklyHours + $plannedHours;
                throw ValidationException::withMessages([
                    'planned_duration_hours' => [
                        "Akumulasi lembur hari kerja minggu berjalan ({$existingWeeklyHours} jam) ditambah rencana ({$plannedHours} jam) menjadi {$totalProjected} jam, melampaui batas maksimal 18 jam/minggu (PP No. 35 Tahun 2021)."
                    ]
                ]);
            }
        }

        $year = $overtimeDate->format('Y');
        $month = $overtimeDate->format('m');
        $count = OvertimeOrder::where('tenant_id', $company->tenant_id)->count() + 1;
        $orderNumber = sprintf('SPL/%s/%s/%04d', $year, $month, $count);

        return OvertimeOrder::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'order_number' => $orderNumber,
            'employee_id' => $employee->id,
            'overtime_date' => $overtimeDate->toDateString(),
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'planned_duration_hours' => $plannedHours,
            'reason_task_description' => $data['reason_task_description'],
            'is_day_off' => $isDayOff,
            'status' => 'pending_approval',
        ]);
    }

    /**
     * Manager approves the SPL order.
     */
    public function approveOrder(OvertimeOrder $order, User $manager): OvertimeOrder
    {
        $order->update([
            'status' => 'approved',
            'manager_approved_by' => $manager->id,
            'manager_approved_at' => now(),
        ]);

        return $order;
    }

    /**
     * Employee gives explicit written/digital consent per PP 35/2021 Pasal 26 ayat 3.
     */
    public function giveEmployeeConsent(OvertimeOrder $order): OvertimeOrder
    {
        $order->update([
            'employee_consented_at' => now(),
        ]);

        return $order;
    }

    /**
     * Reconcile actual overtime hours after work completion.
     */
    public function verifyActualOvertime(OvertimeOrder $order, float $actualHours): OvertimeOrder
    {
        $order->update([
            'actual_duration_hours' => $actualHours,
            'status' => 'completed_verified',
        ]);

        return $order;
    }
}
