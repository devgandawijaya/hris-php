<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\JobCandidate;
use App\Models\JobPosting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RecruitmentService
{
    /**
     * Create a new job vacancy posting.
     */
    public function createPosting(int $tenantId, array $data): JobPosting
    {
        return JobPosting::create([
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'department_id' => $data['department_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'employment_type' => $data['employment_type'] ?? 'full_time',
            'location' => $data['location'] ?? 'Jakarta / Hybrid',
            'description' => $data['description'],
            'requirements' => $data['requirements'] ?? null,
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'status' => $data['status'] ?? 'published',
            'deadline' => $data['deadline'] ?? null,
        ]);
    }

    /**
     * Submit an application for a candidate (registering candidate if not existing).
     */
    public function applyForJob(
        JobPosting $posting,
        array $candidateData,
        ?UploadedFile $resume = null
    ): JobApplication {
        return DB::transaction(function () use ($posting, $candidateData, $resume) {
            $resumePath = null;
            if ($resume) {
                $resumePath = $resume->store("tenants/{$posting->tenant_id}/resumes", 'public');
            }

            // Find or create candidate
            $candidate = JobCandidate::firstOrCreate(
                [
                    'tenant_id' => $posting->tenant_id,
                    'email' => $candidateData['email'],
                ],
                [
                    'first_name' => $candidateData['first_name'],
                    'last_name' => $candidateData['last_name'] ?? null,
                    'phone' => $candidateData['phone'] ?? null,
                    'resume_path' => $resumePath,
                    'linkedin_url' => $candidateData['linkedin_url'] ?? null,
                    'portfolio_url' => $candidateData['portfolio_url'] ?? null,
                ]
            );

            if ($resumePath && empty($candidate->resume_path)) {
                $candidate->update(['resume_path' => $resumePath]);
            }

            // Create application
            return JobApplication::create([
                'tenant_id' => $posting->tenant_id,
                'job_posting_id' => $posting->id,
                'job_candidate_id' => $candidate->id,
                'stage' => 'applied',
            ]);
        });
    }

    /**
     * Advance application stage in pipeline.
     */
    public function advanceStage(JobApplication $application, string $stage, array $data = []): JobApplication
    {
        $update = ['stage' => $stage];

        if ($stage === 'interview') {
            if (!empty($data['interview_scheduled_at'])) {
                $update['interview_scheduled_at'] = $data['interview_scheduled_at'];
            }
            if (!empty($data['interview_notes'])) {
                $update['interview_notes'] = $data['interview_notes'];
            }
            if (isset($data['interview_score'])) {
                $update['interview_score'] = $data['interview_score'];
            }
        } elseif ($stage === 'offered') {
            if (!empty($data['offered_salary'])) {
                $update['offered_salary'] = $data['offered_salary'];
            }
        } elseif ($stage === 'rejected') {
            if (!empty($data['rejection_reason'])) {
                $update['rejection_reason'] = $data['rejection_reason'];
            }
        }

        $application->update($update);
        return $application;
    }

    /**
     * Convert hired candidate into active Employee record.
     */
    public function convertToEmployee(JobApplication $application, int $companyId, array $employeeData): Employee
    {
        return DB::transaction(function () use ($application, $companyId, $employeeData) {
            $candidate = $application->candidate;
            $posting = $application->jobPosting;

            $employee = Employee::create([
                'tenant_id' => $application->tenant_id,
                'company_id' => $companyId,
                'employee_number' => $employeeData['employee_number'] ?? ('EMP-' . date('Ymd') . '-' . rand(100, 999)),
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'department_id' => $employeeData['department_id'] ?? $posting->department_id,
                'position_id' => $employeeData['position_id'] ?? $posting->position_id,
                'employment_status' => $employeeData['employment_status'] ?? 'pkwt',
                'join_date' => $employeeData['join_date'] ?? now()->toDateString(),
                'basic_salary' => $employeeData['basic_salary'] ?? ($application->offered_salary ?? 8000000.00),
                'status' => 'active',
            ]);

            $application->update([
                'stage' => 'hired',
                'converted_employee_id' => $employee->id,
            ]);

            return $employee;
        });
    }
}
