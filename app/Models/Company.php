<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'legal_name',
        'npwp',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'logo_path',
    ];

    public function settings(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function jobGrades(): HasMany
    {
        return $this->hasMany(JobGrade::class);
    }

    public function businessTrips(): HasMany
    {
        return $this->hasMany(BusinessTripOrder::class);
    }

    public function offboardingClearances(): HasMany
    {
        return $this->hasMany(OffboardingClearance::class);
    }

    public function probationEvaluations(): HasMany
    {
        return $this->hasMany(ProbationEvaluation::class);
    }

    public function fwaSchedules(): HasMany
    {
        return $this->hasMany(FwaSchedule::class);
    }

    public function workplaceIncidents(): HasMany
    {
        return $this->hasMany(WorkplaceIncident::class);
    }

    public function fringeBenefits(): HasMany
    {
        return $this->hasMany(FringeBenefit::class);
    }

    public function overtimeOrders(): HasMany
    {
        return $this->hasMany(OvertimeOrder::class);
    }

    public function companyRegulations(): HasMany
    {
        return $this->hasMany(CompanyRegulation::class);
    }

    public function bipartiteMeetings(): HasMany
    {
        return $this->hasMany(BipartiteMeeting::class);
    }
}

