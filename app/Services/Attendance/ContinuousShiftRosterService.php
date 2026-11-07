<?php

namespace App\Services\Attendance;

use App\Models\Company;
use App\Models\Employee;
use App\Models\ContinuousShiftRoster;
use App\Models\EmployeeShiftRosterAssignment;
use Carbon\Carbon;
use InvalidArgumentException;

class ContinuousShiftRosterService
{
    /**
     * Create continuous rotational shift pattern (Kepmenakertrans No. KEP-233/MEN/2003)
     */
    public function createRoster(Company $company, array $data): ContinuousShiftRoster
    {
        $tenantId = $company->tenant_id;
        $nextNum = ContinuousShiftRoster::where('tenant_id', $tenantId)->count() + 1;
        $rosterCode = sprintf('ROSTER-%s-%04d', date('Y'), $nextNum);

        $defaultPattern = [
            'groups' => ['Grup A', 'Grup B', 'Grup C', 'Grup D'],
            'shifts' => [
                'Pagi' => ['start' => '07:00', 'end' => '15:00', 'duration' => 8],
                'Siang' => ['start' => '15:00', 'end' => '23:00', 'duration' => 8],
                'Malam' => ['start' => '23:00', 'end' => '07:00', 'duration' => 8],
                'Off' => ['start' => null, 'end' => null, 'duration' => 0],
            ],
            'cycle_summary' => '3 Shift 4 Grup: 6 hari kerja bergilir (2 Pagi, 2 Siang, 2 Malam) diikuti 2 hari istirahat mingguan.',
        ];

        return ContinuousShiftRoster::create([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'roster_code' => $rosterCode,
            'name' => $data['name'],
            'department_id' => $data['department_id'] ?? null,
            'cycle_days' => $data['cycle_days'] ?? 28,
            'work_days_per_cycle' => $data['work_days_per_cycle'] ?? 21,
            'off_days_per_cycle' => $data['off_days_per_cycle'] ?? 7,
            'night_shift_allowance_rate' => $data['night_shift_allowance_rate'] ?? 50000.00,
            'standby_allowance_rate' => $data['standby_allowance_rate'] ?? 75000.00,
            'rotation_pattern_json' => $data['rotation_pattern_json'] ?? $defaultPattern,
            'is_active' => true,
        ]);
    }

    /**
     * Assign employee to a shift roster group
     */
    public function assignEmployeeToGroup(ContinuousShiftRoster $roster, Employee $employee, string $groupName, string $startDate): EmployeeShiftRosterAssignment
    {
        // Deactivate previous active assignments for this roster
        EmployeeShiftRosterAssignment::where('tenant_id', $roster->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->update([
                'status' => 'inactive',
                'effective_end_date' => Carbon::parse($startDate)->subDay()->toDateString(),
            ]);

        return EmployeeShiftRosterAssignment::create([
            'tenant_id' => $roster->tenant_id,
            'continuous_shift_roster_id' => $roster->id,
            'employee_id' => $employee->id,
            'group_name' => $groupName,
            'effective_start_date' => Carbon::parse($startDate)->toDateString(),
            'status' => 'active',
        ]);
    }

    /**
     * Calculate monthly shift allowances for payroll integration
     */
    public function calculateShiftAllowances(Employee $employee, int $nightShiftCount, int $standbyDaysCount, ContinuousShiftRoster $roster): array
    {
        $nightAllowance = round($nightShiftCount * (float) $roster->night_shift_allowance_rate, 2);
        $standbyAllowance = round($standbyDaysCount * (float) $roster->standby_allowance_rate, 2);
        $totalShiftAllowance = $nightAllowance + $standbyAllowance;

        return [
            'night_shift_count' => $nightShiftCount,
            'night_shift_rate' => (float) $roster->night_shift_allowance_rate,
            'night_shift_allowance' => $nightAllowance,
            'standby_days_count' => $standbyDaysCount,
            'standby_rate' => (float) $roster->standby_allowance_rate,
            'standby_allowance' => $standbyAllowance,
            'total_shift_allowance' => $totalShiftAllowance,
        ];
    }
}
