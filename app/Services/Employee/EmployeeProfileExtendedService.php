<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\EmployeeEducation;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeEmploymentHistory;
use App\Models\EmployeeFamily;

class EmployeeProfileExtendedService
{
    /**
     * Add family member.
     */
    public function addFamilyMember(Employee $employee, array $data): EmployeeFamily
    {
        return EmployeeFamily::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'name' => $data['name'],
            'relationship' => $data['relationship'],
            'gender' => $data['gender'] ?? 'male',
            'birth_date' => $data['birth_date'] ?? null,
            'nik' => $data['nik'] ?? null,
            'education_level' => $data['education_level'] ?? null,
            'is_bpjs_covered' => (bool) ($data['is_bpjs_covered'] ?? false),
            'is_tax_dependent' => (bool) ($data['is_tax_dependent'] ?? false),
            'contact_number' => $data['contact_number'] ?? null,
        ]);
    }

    /**
     * Add education history.
     */
    public function addEducation(Employee $employee, array $data): EmployeeEducation
    {
        return EmployeeEducation::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'degree_level' => $data['degree_level'],
            'institution_name' => $data['institution_name'],
            'major' => $data['major'] ?? null,
            'graduation_year' => (int) $data['graduation_year'],
            'gpa' => isset($data['gpa']) ? (float) $data['gpa'] : null,
            'certificate_number' => $data['certificate_number'] ?? null,
        ]);
    }

    /**
     * Add previous employment history.
     */
    public function addEmploymentHistory(Employee $employee, array $data): EmployeeEmploymentHistory
    {
        return EmployeeEmploymentHistory::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'company_name' => $data['company_name'],
            'position_title' => $data['position_title'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'last_salary' => isset($data['last_salary']) ? (float) $data['last_salary'] : null,
            'leaving_reason' => $data['leaving_reason'] ?? null,
        ]);
    }

    /**
     * Add emergency contact.
     */
    public function addEmergencyContact(Employee $employee, array $data): EmployeeEmergencyContact
    {
        return EmployeeEmergencyContact::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'contact_name' => $data['contact_name'],
            'relationship' => $data['relationship'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'is_primary' => (bool) ($data['is_primary'] ?? true),
        ]);
    }

    /**
     * Assign supervisor (direct reporting manager).
     */
    public function assignSupervisor(Employee $employee, ?int $supervisorId): Employee
    {
        $employee->reports_to_id = $supervisorId;
        $employee->save();

        return $employee->fresh();
    }
}
