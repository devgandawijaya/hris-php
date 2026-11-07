<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'legal_name',
        'npwp',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'industry',
        'logo_path',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

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

    // --- PHASE 15: WLTK, IJP, MENTORSHIP, ASSET BAST & DISABILITY AUDIT ---

    public function wltkReports(): HasMany
    {
        return $this->hasMany(WltkReport::class);
    }

    public function ijpVacancies(): HasMany
    {
        return $this->hasMany(IjpVacancy::class);
    }

    public function mentorshipPairs(): HasMany
    {
        return $this->hasMany(MentorshipPair::class);
    }

    public function assetHandoverBasts(): HasMany
    {
        return $this->hasMany(AssetHandoverBast::class);
    }

    public function disabilityEmploymentAudits(): HasMany
    {
        return $this->hasMany(DisabilityEmploymentAudit::class);
    }

    public function employeeDisabilityProfiles(): HasMany
    {
        return $this->hasMany(EmployeeDisabilityProfile::class);
    }

    // --- PHASE 16: TKA, JKP, KOPERASI, CONTINUOUS ROSTER & PHI DISPUTES ---

    public function expatriatePermits(): HasMany
    {
        return $this->hasMany(ExpatriatePermit::class);
    }

    public function jkpCertifications(): HasMany
    {
        return $this->hasMany(JkpClaimCertification::class);
    }

    public function employeeCooperatives(): HasMany
    {
        return $this->hasMany(EmployeeCooperative::class);
    }

    public function continuousShiftRosters(): HasMany
    {
        return $this->hasMany(ContinuousShiftRoster::class);
    }

    public function tripartiteIndustrialDisputes(): HasMany
    {
        return $this->hasMany(TripartiteIndustrialDispute::class);
    }

    // --- PHASE 17: OUTSOURCING, PKB, PPKS, VSS & INTERNSHIP ---

    public function outsourcingVendors(): HasMany
    {
        return $this->hasMany(OutsourcingVendor::class);
    }

    public function laborUnions(): HasMany
    {
        return $this->hasMany(LaborUnion::class);
    }

    public function pkbNegotiations(): HasMany
    {
        return $this->hasMany(PkbNegotiation::class);
    }

    public function ppksComplaintCases(): HasMany
    {
        return $this->hasMany(PpksComplaintCase::class);
    }

    public function femaleWorkerFacilityChecks(): HasMany
    {
        return $this->hasMany(FemaleWorkerFacilityCheck::class);
    }

    public function voluntarySeparationPrograms(): HasMany
    {
        return $this->hasMany(VoluntarySeparationProgram::class);
    }

    public function dplkParticipations(): HasMany
    {
        return $this->hasMany(DplkParticipation::class);
    }

    public function internshipPrograms(): HasMany
    {
        return $this->hasMany(InternshipProgram::class);
    }

    // --- PHASE 18: FLEX BENEFITS, STATUTORY THR, ESOP, COB & P2K3 ---

    public function flexBenefitCategories(): HasMany
    {
        return $this->hasMany(FlexBenefitCategory::class);
    }

    public function flexBenefitAllocations(): HasMany
    {
        return $this->hasMany(FlexBenefitAllocation::class);
    }

    public function thrDisbursements(): HasMany
    {
        return $this->hasMany(ThrDisbursement::class);
    }

    public function esopGrants(): HasMany
    {
        return $this->hasMany(EsopGrant::class);
    }

    public function cobInsurancePolicies(): HasMany
    {
        return $this->hasMany(CobInsurancePolicy::class);
    }

    public function p2k3Committees(): HasMany
    {
        return $this->hasMany(P2k3Committee::class);
    }

    // --- PHASE 19: ESG SUSTAINABILITY, COMMUTER CARBON & ERGONOMICS ---

    public function esgMetricSnapshots(): HasMany
    {
        return $this->hasMany(EsgMetricSnapshot::class);
    }

    public function employeeCommuteProfiles(): HasMany
    {
        return $this->hasMany(EmployeeCommuteProfile::class);
    }

    public function commuteEmissionAudits(): HasMany
    {
        return $this->hasMany(CommuteEmissionAudit::class);
    }

    public function ergonomicAssessments(): HasMany
    {
        return $this->hasMany(ErgonomicAssessment::class);
    }

    public function digitalStressSurveys(): HasMany
    {
        return $this->hasMany(DigitalStressSurvey::class);
    }

    // --- PHASE 20: DISASTER RECOVERY, MULTI-REGION FAILOVER & UU PDP LAW ---

    public function pdpConsentLogs(): HasMany
    {
        return $this->hasMany(PdpConsentLog::class);
    }

    public function pdpDsarRequests(): HasMany
    {
        return $this->hasMany(PdpDsarRequest::class);
    }

    public function pdpBreachIncidentLogs(): HasMany
    {
        return $this->hasMany(PdpBreachIncidentLog::class);
    }

    public function disasterRecoveryConfig(): HasOne
    {
        return $this->hasOne(DisasterRecoveryConfig::class);
    }

    public function disasterRecoveryDrillLogs(): HasMany
    {
        return $this->hasMany(DisasterRecoveryDrillLog::class);
    }
}

