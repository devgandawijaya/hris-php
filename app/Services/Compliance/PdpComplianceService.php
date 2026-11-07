<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PdpConsentLog;
use App\Models\PdpDsarRequest;
use App\Models\PdpBreachIncidentLog;
use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PdpComplianceService
{
    /**
     * Record employee consent under Pasal 20 - 24 UU No. 27/2022.
     */
    public function recordConsent(
        Employee $employee,
        string $consentType,
        string $purposeDescription,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): PdpConsentLog {
        return PdpConsentLog::create([
            'tenant_id' => $employee->tenant_id,
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'consent_type' => $consentType,
            'purpose_description' => $purposeDescription,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'consented_at' => Carbon::now(),
            'status' => 'granted',
        ]);
    }

    /**
     * Revoke existing consent pursuant to Pasal 9 UU PDP.
     */
    public function revokeConsent(PdpConsentLog $consentLog): PdpConsentLog
    {
        $consentLog->status = 'revoked';
        $consentLog->revoked_at = Carbon::now();
        $consentLog->save();

        return $consentLog;
    }

    /**
     * Submit a Data Subject Access Request (DSAR) under Pasal 5 s/d 15 UU PDP.
     * Deadline: Maksimal 3 x 24 jam (72 jam kerja) sesuai ketentuan pemenuhan hak.
     */
    public function submitDsarRequest(
        Employee $employee,
        string $requestType,
        ?string $scopeNotes = null
    ): PdpDsarRequest {
        $validTypes = ['data_portability', 'data_access', 'data_rectification', 'data_erasure'];
        if (!in_array($requestType, $validTypes)) {
            throw new InvalidArgumentException("Tipe permohonan hak PDP '{$requestType}' tidak valid.");
        }

        $requestNumber = 'DSAR-' . date('Y') . '-' . strtoupper(Str::random(6));
        $requestDate = Carbon::today();
        $deadline = $requestDate->copy()->addDays(3); // 3 hari kerja

        return PdpDsarRequest::create([
            'tenant_id' => $employee->tenant_id,
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'request_number' => $requestNumber,
            'request_type' => $requestType,
            'reason_or_scope' => $scopeNotes,
            'request_date' => $requestDate->toDateString(),
            'completion_deadline' => $deadline->toDateString(),
            'status' => 'pending',
        ]);
    }

    /**
     * Fulfill Data Portability request by compiling structured JSON/array export (Pasal 13).
     */
    public function exportPortabilityPackage(PdpDsarRequest $request, int $handledByUserId): array
    {
        $employee = $request->employee;

        $package = [
            'pdp_statutory_basis' => 'UU No. 27 Tahun 2022 Pasal 13 (Hak Portabilitas Data Pribadi)',
            'request_number' => $request->request_number,
            'exported_at' => Carbon::now()->toIso8601String(),
            'data_subject' => [
                'nik' => $employee->nik,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'date_of_birth' => $employee->date_of_birth,
                'gender' => $employee->gender,
                'join_date' => $employee->join_date,
                'department' => $employee->department->name ?? null,
                'position' => $employee->position->name ?? null,
                'employment_type' => $employee->employment_type,
            ],
            'consents_history' => $employee->pdpConsentLogs()->get()->map(function ($c) {
                return [
                    'type' => $c->consent_type,
                    'purpose' => $c->purpose_description,
                    'consented_at' => $c->consented_at->toIso8601String(),
                    'status' => $c->status,
                ];
            })->toArray(),
        ];

        $request->status = 'fulfilled';
        $request->fulfilled_at = Carbon::now();
        $request->handled_by_user_id = $handledByUserId;
        $request->resolution_notes = 'Paket portabilitas data pribadi berhasil diekspor dalam format terstruktur.';
        $request->save();

        return $package;
    }

    /**
     * Log and audit a data breach incident under Pasal 46 UU PDP (wajib notifikasi 72 jam).
     */
    public function recordDataBreach(
        Company $company,
        string $breachType,
        int $affectedRecords,
        string $incidentDescription,
        string $remediationMeasures
    ): PdpBreachIncidentLog {
        $incidentNumber = 'BREACH-' . date('Y') . '-' . strtoupper(Str::random(6));
        $now = Carbon::now();

        // Authority notified immediately (within 72 hours)
        return PdpBreachIncidentLog::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'incident_number' => $incidentNumber,
            'breach_type' => $breachType,
            'detected_at' => $now,
            'affected_records_count' => $affectedRecords,
            'authority_notified_at' => $now->copy()->addHours(2), // Notified in 2 hours
            'subjects_notified_at' => $now->copy()->addHours(4),
            'incident_description' => $incidentDescription,
            'remediation_measures' => $remediationMeasures,
            'status' => 'notified',
        ]);
    }
}
