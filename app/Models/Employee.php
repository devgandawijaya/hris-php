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
}
