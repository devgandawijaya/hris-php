<?php

namespace App\Services\EmployeeRelations;

use App\Models\Employee;
use App\Models\EmployeeGrievance;
use App\Models\User;
use Illuminate\Support\Carbon;

class GrievanceService
{
    /**
     * Submit a confidential employee grievance or whistleblowing report.
     */
    public function submitGrievance(Employee $employee, array $data): EmployeeGrievance
    {
        return EmployeeGrievance::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'category' => $data['category'] ?? 'other',
            'subject' => $data['subject'],
            'description' => $data['description'],
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
            'status' => 'submitted',
        ]);
    }

    /**
     * Update grievance investigation status.
     */
    public function updateStatus(EmployeeGrievance $grievance, string $status, ?string $notes = null): EmployeeGrievance
    {
        $grievance->update([
            'status' => $status,
            'resolution_notes' => $notes ?? $grievance->resolution_notes,
        ]);

        return $grievance->fresh();
    }

    /**
     * Resolve and close an employee grievance with recorded mediation notes.
     */
    public function resolveGrievance(EmployeeGrievance $grievance, User $resolver, string $resolutionNotes): EmployeeGrievance
    {
        $grievance->update([
            'status' => 'resolved',
            'resolution_notes' => $resolutionNotes,
            'resolved_by_user_id' => $resolver->id,
            'resolved_at' => Carbon::now(),
        ]);

        return $grievance->fresh();
    }
}

