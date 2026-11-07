<?php

namespace App\Services\Organization;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Collection;

class OrgChartService
{
    /**
     * Build nested hierarchical organization tree for a company.
     */
    public function buildHierarchy(Company $company, ?int $departmentId = null): array
    {
        $query = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->with(['department', 'position', 'jobGrade']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->get();

        // Group by reports_to_id
        $grouped = $employees->groupBy('reports_to_id');

        // Root employees are those whose reports_to_id is null or not found in current employee list
        $employeeIds = $employees->pluck('id')->all();
        $roots = $employees->filter(function ($emp) use ($employeeIds) {
            return is_null($emp->reports_to_id) || !in_array($emp->reports_to_id, $employeeIds);
        });

        $tree = [];
        foreach ($roots as $root) {
            $tree[] = $this->buildNode($root, $grouped);
        }

        return [
            'total_headcount' => $employees->count(),
            'tree' => $tree,
        ];
    }

    /**
     * Recursively build tree nodes with headcount.
     */
    private function buildNode(Employee $employee, Collection $grouped): array
    {
        $subordinates = $grouped->get($employee->id, collect());
        $children = [];
        $totalDescendants = 0;

        foreach ($subordinates as $sub) {
            $childNode = $this->buildNode($sub, $grouped);
            $totalDescendants += 1 + $childNode['indirect_reports_count'];
            $children[] = $childNode;
        }

        return [
            'id' => $employee->id,
            'name' => $employee->full_name,
            'employee_number' => $employee->employee_number,
            'position' => $employee->position ? $employee->position->title : 'Staff',
            'department' => $employee->department ? $employee->department->name : '-',
            'grade' => $employee->jobGrade ? $employee->jobGrade->grade_code : null,
            'email' => $employee->email,
            'photo' => $employee->photo_path,
            'direct_reports_count' => $subordinates->count(),
            'indirect_reports_count' => $totalDescendants,
            'children' => $children,
        ];
    }
}
