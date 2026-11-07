<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\IjpVacancy;
use App\Models\IjpApplication;
use App\Models\PerformanceReview;
use Carbon\Carbon;
use InvalidArgumentException;

class IjpMarketplaceService
{
    /**
     * Create a new Internal Job Posting (IJP) vacancy
     */
    public function createVacancy(array $data, int $userId): IjpVacancy
    {
        $tenantId = $data['tenant_id'];
        $nextNum = IjpVacancy::where('tenant_id', $tenantId)->count() + 1;
        $vacancyCode = sprintf('IJP-%s-%04d', date('Y'), $nextNum);

        return IjpVacancy::create([
            'tenant_id' => $tenantId,
            'company_id' => $data['company_id'],
            'department_id' => $data['department_id'],
            'position_id' => $data['position_id'],
            'vacancy_code' => $vacancyCode,
            'title' => $data['title'],
            'description' => $data['description'],
            'requirements' => $data['requirements'],
            'min_tenure_months' => $data['min_tenure_months'] ?? 12,
            'min_performance_score' => $data['min_performance_score'] ?? 3.00,
            'open_slots' => $data['open_slots'] ?? 1,
            'application_deadline' => $data['application_deadline'],
            'status' => 'open',
            'created_by_user_id' => $userId,
        ]);
    }

    /**
     * Check if an employee is eligible to apply for an IJP vacancy
     */
    public function validateEligibility(Employee $employee, IjpVacancy $vacancy): array
    {
        // 1. Calculate tenure in months
        $joinDate = $employee->join_date ? Carbon::parse($employee->join_date) : Carbon::today();
        $tenureMonths = max(0, $joinDate->diffInMonths(Carbon::today()));

        // 2. Check active warnings (Surat Peringatan)
        $hasActiveWarning = $employee->activeWarnings()->exists();

        // 3. Check latest performance score
        $latestReview = PerformanceReview::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->latest('review_period_end')
            ->first();

        $performanceScore = $latestReview ? (float) ($latestReview->final_score ?? $latestReview->score ?? 3.50) : 3.50;

        $isEligible = true;
        $reasons = [];

        if ($tenureMonths < $vacancy->min_tenure_months) {
            $isEligible = false;
            $reasons[] = "Masa kerja belum memenuhi syarat minimum ({$tenureMonths} dari {$vacancy->min_tenure_months} bulan).";
        }

        if ($hasActiveWarning) {
            $isEligible = false;
            $reasons[] = "Karyawan memiliki Surat Peringatan (SP) yang masih aktif.";
        }

        if ($performanceScore < (float) $vacancy->min_performance_score) {
            $isEligible = false;
            $reasons[] = "Nilai evaluasi kinerja terakhir ({$performanceScore}) berada di bawah standar minimum ({$vacancy->min_performance_score}).";
        }

        return [
            'is_eligible' => $isEligible,
            'tenure_months' => $tenureMonths,
            'has_active_warning' => $hasActiveWarning,
            'performance_score' => $performanceScore,
            'reasons' => $reasons,
        ];
    }

    /**
     * Submit application for an IJP vacancy
     */
    public function applyVacancy(Employee $employee, IjpVacancy $vacancy, array $data): IjpApplication
    {
        // Check if already applied
        $existing = IjpApplication::where('tenant_id', $employee->tenant_id)
            ->where('ijp_vacancy_id', $vacancy->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException("Anda sudah mengajukan lamaran untuk lowongan internal ini.");
        }

        $eligibility = $this->validateEligibility($employee, $vacancy);

        return IjpApplication::create([
            'tenant_id' => $employee->tenant_id,
            'ijp_vacancy_id' => $vacancy->id,
            'employee_id' => $employee->id,
            'current_department_id' => $employee->department_id,
            'current_position_id' => $employee->position_id,
            'application_date' => Carbon::today()->toDateString(),
            'motivation_letter' => $data['motivation_letter'],
            'portfolio_url' => $data['portfolio_url'] ?? null,
            'current_tenure_months' => $eligibility['tenure_months'],
            'latest_performance_score' => $eligibility['performance_score'],
            'has_active_warning' => $eligibility['has_active_warning'],
            'is_eligible' => $eligibility['is_eligible'],
            'ineligibility_reason' => !empty($eligibility['reasons']) ? implode(' ', $eligibility['reasons']) : null,
            'manager_approval_status' => 'pending',
            'hr_interview_status' => 'pending',
            'final_decision' => 'in_review',
        ]);
    }

    /**
     * Origin Department Manager approval/release
     */
    public function reviewManagerApproval(IjpApplication $application, bool $approved, ?string $notes = null): IjpApplication
    {
        $application->update([
            'manager_approval_status' => $approved ? 'approved' : 'rejected',
            'manager_approval_notes' => $notes,
            'manager_approved_at' => now(),
            'final_decision' => $approved ? 'in_review' : 'rejected',
        ]);

        return $application->fresh();
    }

    /**
     * HR decision on internal mobility
     */
    public function reviewHrDecision(IjpApplication $application, string $decision, ?string $effectiveDate = null, ?string $notes = null): IjpApplication
    {
        $updateData = [
            'final_decision' => $decision, // offered, accepted, rejected
            'hr_interview_notes' => $notes,
        ];

        if ($decision === 'accepted' || $decision === 'offered') {
            $updateData['hr_interview_status'] = 'passed';
            if ($effectiveDate) {
                $updateData['transfer_effective_date'] = Carbon::parse($effectiveDate)->toDateString();
            }
        } elseif ($decision === 'rejected') {
            $updateData['hr_interview_status'] = 'failed';
        }

        $application->update($updateData);

        return $application->fresh();
    }
}
