<?php

namespace App\Services\Career;

use App\Models\Employee;
use App\Models\EmployeeMutation;
use App\Models\User;
use App\Services\Notification\AppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CareerTransitionService
{
    public function __construct(
        protected AppNotificationService $notificationService
    ) {}

    /**
     * Record an employee mutation, promotion, demotion, or salary adjustment
     */
    public function recordTransition(Employee $employee, array $data, ?User $author = null): EmployeeMutation
    {
        return DB::transaction(function () use ($employee, $data, $author) {
            $effectiveDate = Carbon::parse($data['effective_date']);

            $mutation = EmployeeMutation::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'type' => $data['type'], // promotion, demotion, transfer, salary_adjustment
                'from_department_id' => $employee->department_id,
                'to_department_id' => $data['to_department_id'] ?? $employee->department_id,
                'from_position_id' => $employee->position_id,
                'to_position_id' => $data['to_position_id'] ?? $employee->position_id,
                'from_salary' => $employee->base_salary,
                'to_salary' => isset($data['to_salary']) ? (float)$data['to_salary'] : $employee->base_salary,
                'effective_date' => $effectiveDate->toDateString(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $author?->id,
            ]);

            // Update employee master record with new department, position, or salary
            $updatePayload = [];
            if (!empty($data['to_department_id'])) {
                $updatePayload['department_id'] = $data['to_department_id'];
            }
            if (!empty($data['to_position_id'])) {
                $updatePayload['position_id'] = $data['to_position_id'];
            }
            if (isset($data['to_salary'])) {
                $updatePayload['base_salary'] = (float)$data['to_salary'];
            }

            if (!empty($updatePayload)) {
                $employee->update($updatePayload);
            }

            // Send notification to employee
            if ($employee->user) {
                $typeLabel = match ($data['type']) {
                    'promotion' => 'Promosi Jabatan',
                    'demotion' => 'Demosi / Pembinaan Karir',
                    'transfer' => 'Mutasi / Rotasi Divisi',
                    'salary_adjustment' => 'Penyesuaian Gaji Pokok',
                    default => 'Perubahan Status Karir',
                };

                $this->notificationService->sendToUser(
                    $employee->user,
                    "Pemberitahuan {$typeLabel}",
                    "Perubahan status karir Anda telah resmi dicatat dan berlaku efektif per {$effectiveDate->format('d M Y')}.",
                    'success',
                    route('ess.dashboard')
                );
            }

            return $mutation;
        });
    }
}
