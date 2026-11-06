<?php

namespace App\Services\EmployeeRelations;

use App\Models\Employee;
use App\Models\EmployeeWarning;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DisciplinaryService
{
    /**
     * Issue a formal warning letter (SP1, SP2, SP3) pursuant to Pasal 161 UU Ketenagakerjaan.
     * The statutory validity period is strictly 6 months from the date of issuance.
     */
    public function issueWarning(Employee $employee, array $data, User $authorizer): EmployeeWarning
    {
        $issuedDate = Carbon::parse($data['issued_date']);
        $validUntil = $issuedDate->copy()->addMonths(6);

        $seq = str_pad(EmployeeWarning::where('tenant_id', $employee->tenant_id)->count() + 1, 4, '0', STR_PAD_LEFT);
        $levelUpper = strtoupper($data['warning_level']); // SP1, SP2, SP3
        $letterNo = "{$levelUpper}/DIR-HR/" . date('Y') . "/{$seq}";

        return EmployeeWarning::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'warning_level' => strtolower($data['warning_level']),
            'letter_number' => $letterNo,
            'issued_date' => $issuedDate->toDateString(),
            'valid_until' => $validUntil->toDateString(),
            'reason' => $data['reason'],
            'action_plan' => $data['action_plan'] ?? null,
            'authorized_by_user_id' => $authorizer->id,
            'status' => 'active',
            'file_path' => $data['file_path'] ?? null,
        ]);
    }

    /**
     * Revoke an active warning letter due to successful performance improvement or dispute resolution.
     */
    public function revokeWarning(EmployeeWarning $warning, ?string $reason = null): EmployeeWarning
    {
        $warning->update([
            'status' => 'revoked',
            'action_plan' => ($warning->action_plan ? $warning->action_plan . " | " : "") . "Dicabut: " . ($reason ?? 'Pemulihan performa'),
        ]);

        return $warning->fresh();
    }

    /**
     * Retrieve currently active warning letters for an employee.
     */
    public function getActiveWarningsForEmployee(Employee $employee): Collection
    {
        return EmployeeWarning::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->where('valid_until', '>=', now()->toDateString())
            ->orderBy('issued_date', 'desc')
            ->get();
    }
}
