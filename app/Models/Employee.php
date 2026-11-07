<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'user_id',
        'employee_number',
        'nik_ktp',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'birth_place',
        'birth_date',
        'marital_status',
        'tax_ptkp_status',
        'npwp',
        'bpjs_tk_number',
        'bpjs_kes_number',
        'department_id',
        'position_id',
        'job_grade_id',
        'reports_to_id',
        'employment_status',
        'join_date',
        'end_date',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'basic_salary',
        'photo_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'join_date' => 'date',
            'end_date' => 'date',
            'basic_salary' => 'decimal:2',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getPositionTitleAttribute(): string
    {
        return $this->position?->title ?? 'Staff';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(EmployeeMutation::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function reimbursements(): HasMany
    {
        return $this->hasMany(Reimbursement::class);
    }

    public function employmentContracts(): HasMany
    {
        return $this->hasMany(EmploymentContract::class);
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(EmploymentContract::class)->where('status', 'active')->latestOfMany();
    }

    public function thrRecords(): HasMany
    {
        return $this->hasMany(ThrRecord::class);
    }

    public function taxReconciliations(): HasMany
    {
        return $this->hasMany(TaxReconciliation::class);
    }

    // --- PHASE 4: TALENT RELATIONS ---

    public function companyAssets(): HasMany
    {
        return $this->hasMany(CompanyAsset::class, 'assigned_employee_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(OnboardingOffboardingChecklist::class);
    }

    public function exitInterview(): HasOne
    {
        return $this->hasOne(ExitInterview::class);
    }

    public function kpiGoals(): HasMany
    {
        return $this->hasMany(KpiGoal::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function trainingEnrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class);
    }

    // --- PHASE 6: RELATIONS & ENGAGEMENT ---

    public function warnings(): HasMany
    {
        return $this->hasMany(EmployeeWarning::class)->latest('issued_date');
    }

    public function activeWarnings(): HasMany
    {
        return $this->hasMany(EmployeeWarning::class)->where('status', 'active')->where('valid_until', '>=', now()->toDateString());
    }

    public function grievances(): HasMany
    {
        return $this->hasMany(EmployeeGrievance::class)->latest();
    }

    public function helpdeskTickets(): HasMany
    {
        return $this->hasMany(HrHelpdeskTicket::class)->latest();
    }

    // --- PHASE 7: TERMINATIONS & SEVERANCE ---

    public function terminations(): HasMany
    {
        return $this->hasMany(EmployeeTermination::class)->latest('termination_date');
    }

    // --- PHASE 8: FINANCIAL WELL-BEING & RECOGNITION ---

    public function loans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class)->latest();
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class)->whereIn('status', ['approved', 'active']);
    }

    public function recognitions(): HasMany
    {
        return $this->hasMany(EmployeeRecognition::class)->latest();
    }

    // --- PHASE 9: COMPETENCY & SUCCESSION ---

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function successionNominations(): HasMany
    {
        return $this->hasMany(SuccessionCandidate::class, 'candidate_employee_id');
    }

    // --- PHASE 10: EXTENDED PROFILE, 360 FEEDBACK & PIP ---

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reports_to_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'reports_to_id');
    }

    public function families(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function employmentHistories(): HasMany
    {
        return $this->hasMany(EmployeeEmploymentHistory::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function feedbacks360(): HasMany
    {
        return $this->hasMany(PerformanceFeedback360::class, 'subject_employee_id');
    }

    public function givenFeedbacks360(): HasMany
    {
        return $this->hasMany(PerformanceFeedback360::class, 'reviewer_employee_id');
    }

    public function pips(): HasMany
    {
        return $this->hasMany(PerformanceImprovementPlan::class);
    }

    public function supervisedPips(): HasMany
    {
        return $this->hasMany(PerformanceImprovementPlan::class, 'supervisor_employee_id');
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function businessTrips(): HasMany
    {
        return $this->hasMany(BusinessTripOrder::class);
    }

    public function offboardingClearances(): HasMany
    {
        return $this->hasMany(OffboardingClearance::class);
    }

    public function paklaringLetters(): HasMany
    {
        return $this->hasMany(WorkExperienceLetter::class);
    }

    public function taxForms1721A1(): HasMany
    {
        return $this->hasMany(TaxForm1721A1Record::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmploymentContract::class);
    }

    public function probationEvaluations(): HasMany
    {
        return $this->hasMany(ProbationEvaluation::class);
    }

    public function fwaSchedules(): HasMany
    {
        return $this->hasMany(FwaSchedule::class);
    }
}
