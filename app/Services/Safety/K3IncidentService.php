<?php

namespace App\Services\Safety;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkplaceIncident;
use App\Models\BpjsJkkClaim;
use App\Models\EmployeeMcuRecord;

class K3IncidentService
{
    /**
     * Record a new workplace safety incident.
     */
    public function reportIncident(Company $company, Employee $victim, array $data, ?User $reporter = null): WorkplaceIncident
    {
        $year = date('Y');
        $count = WorkplaceIncident::where('tenant_id', $company->tenant_id)
            ->whereYear('incident_date', $year)
            ->count() + 1;

        $incidentNumber = sprintf('INC/K3/%s/%04d', $year, $count);

        return WorkplaceIncident::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'incident_number' => $incidentNumber,
            'incident_date' => $data['incident_date'],
            'incident_time' => $data['incident_time'] ?? null,
            'location' => $data['location'],
            'employee_id' => $victim->id,
            'severity' => $data['severity'] ?? 'minor_first_aid',
            'injury_type' => $data['injury_type'] ?? null,
            'lost_workdays' => $data['lost_workdays'] ?? 0,
            'description' => $data['description'],
            'root_cause_analysis' => $data['root_cause_analysis'] ?? null,
            'corrective_actions' => $data['corrective_actions'] ?? null,
            'status' => 'reported',
            'reported_by_user_id' => $reporter?->id,
        ]);
    }

    /**
     * File BPJS Ketenagakerjaan JKK Claim (Tahap 1 / Tahap 2).
     */
    public function fileBpjsJkkClaim(WorkplaceIncident $incident, array $claimData): BpjsJkkClaim
    {
        $year = date('Y');
        $count = BpjsJkkClaim::where('tenant_id', $incident->tenant_id)->count() + 1;
        $claimNumber = sprintf('JKK-BPJS-%s-%04d', $year, $count);

        // STMB (Santunan Sementara Tidak Mampu Bekerja): 100% upah untuk masa pemulihan awal
        $victim = $incident->employee;
        $monthlyWage = (float) ($victim->basic_salary ?? 5000000);
        $stmbAllowance = round(($monthlyWage / 30) * max(1, $incident->lost_workdays), 2);

        return BpjsJkkClaim::create([
            'tenant_id' => $incident->tenant_id,
            'workplace_incident_id' => $incident->id,
            'claim_number' => $claimNumber,
            'stage' => $claimData['stage'] ?? 'tahap_1',
            'medical_facility_name' => $claimData['medical_facility_name'] ?? 'RS PLKK Mitra BPJS',
            'estimated_medical_expense' => $claimData['estimated_medical_expense'] ?? 0.00,
            'approved_bpjs_coverage' => $claimData['approved_bpjs_coverage'] ?? 0.00,
            'temporary_disability_allowance' => $stmbAllowance,
            'claim_status' => 'submitted_to_bpjs',
            'submitted_at' => now()->toDateString(),
            'bpjs_notes' => $claimData['bpjs_notes'] ?? 'Laporan kecelakaan kerja terdaftar resmi ke BPJS Ketenagakerjaan.',
        ]);
    }

    /**
     * Settle and close approved BPJS JKK Claim.
     */
    public function settleJkkClaim(BpjsJkkClaim $claim, float $approvedCoverage, ?string $notes = null): BpjsJkkClaim
    {
        $claim->update([
            'approved_bpjs_coverage' => $approvedCoverage,
            'claim_status' => 'reimbursed',
            'settled_at' => now()->toDateString(),
            'bpjs_notes' => $notes ?? 'Klaim JKK telah diverifikasi dan dana penggantian perawatan telah dicairkan BPJS.',
        ]);

        $claim->incident?->update(['status' => 'closed']);

        return $claim;
    }

    /**
     * Record employee Medical Check-Up (MCU) health surveillance.
     */
    public function recordMcu(Employee $employee, array $mcuData): EmployeeMcuRecord
    {
        return EmployeeMcuRecord::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'mcu_date' => $mcuData['mcu_date'],
            'clinic_provider' => $mcuData['clinic_provider'],
            'blood_pressure' => $mcuData['blood_pressure'] ?? null,
            'cholesterol' => $mcuData['cholesterol'] ?? null,
            'blood_sugar' => $mcuData['blood_sugar'] ?? null,
            'fitness_category' => $mcuData['fitness_category'] ?? 'fit_to_work',
            'doctor_notes' => $mcuData['doctor_notes'] ?? null,
            'follow_up_recommendation' => $mcuData['follow_up_recommendation'] ?? null,
        ]);
    }

    /**
     * Get aggregate K3 metrics for the company.
     */
    public function getK3Metrics(Company $company, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');

        $incidents = WorkplaceIncident::where('company_id', $company->id)
            ->whereYear('incident_date', $year)
            ->get();

        $totalIncidents = $incidents->count();
        $totalLostWorkdays = (int) $incidents->sum('lost_workdays');
        $ltiCount = $incidents->where('severity', 'lost_time_injury_lti')->count();
        $nearMissCount = $incidents->where('severity', 'near_miss')->count();

        return [
            'year' => $year,
            'total_incidents' => $totalIncidents,
            'total_lost_workdays' => $totalLostWorkdays,
            'lti_count' => $ltiCount,
            'near_miss_count' => $nearMissCount,
            'open_investigations' => $incidents->whereIn('status', ['reported', 'investigating'])->count(),
        ];
    }
}
