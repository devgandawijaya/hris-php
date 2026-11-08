<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\Employee;
use App\Models\ExpatriatePermit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ExpatriatePermitService
{
    /**
     * Register RPTKA & Expatriate Work Permit (PP No. 34 Tahun 2021)
     */
    public function registerPermit(Company $company, Employee $tkaEmployee, array $data): ExpatriatePermit
    {
        $tenantId = $company->tenant_id;

        // Indonesian statutory check: TKA is strictly prohibited from holding HR/personalia positions (Pasal 46 UU Ketenagakerjaan / Kepmenaker 349/2019)
        $prohibitedKeywords = ['human resource', 'hr', 'personalia', 'industrial relation', 'personel'];
        $posTitleLower = strtolower($data['job_position_title']);
        foreach ($prohibitedKeywords as $keyword) {
            if (str_contains($posTitleLower, $keyword)) {
                throw new InvalidArgumentException("TKA dilarang menduduki jabatan yang membidangi personalia/HR sesuai ketentuan Pasal 46 UU Ketenagakerjaan jo. Kepmenaker No. 349/2019.");
            }
        }

        // TKA must have WNA citizenship
        $tkaEmployee->update(['citizenship' => 'WNA']);

        $rptkaStart = Carbon::parse($data['rptka_approval_date']);
        $rptkaEnd = Carbon::parse($data['rptka_expiry_date']);

        return ExpatriatePermit::create([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'employee_id' => $tkaEmployee->id,
            'rptka_number' => $data['rptka_number'],
            'rptka_approval_date' => $rptkaStart->toDateString(),
            'rptka_expiry_date' => $rptkaEnd->toDateString(),
            'job_position_title' => $data['job_position_title'],
            'passport_number' => $data['passport_number'],
            'passport_expiry_date' => Carbon::parse($data['passport_expiry_date'])->toDateString(),
            'itas_vitas_number' => $data['itas_vitas_number'] ?? null,
            'itas_expiry_date' => !empty($data['itas_expiry_date']) ? Carbon::parse($data['itas_expiry_date'])->toDateString() : null,
            'dkptka_monthly_amount_usd' => $data['dkptka_monthly_amount_usd'] ?? 100.00,
            'dkptka_payment_proof' => $data['dkptka_payment_proof'] ?? null,
            'companion_employee_id' => $data['companion_employee_id'] ?? null,
            'technology_transfer_plan' => $data['technology_transfer_plan'] ?? 'Program mentoring alih keahlian dan pendampingan teknologi kepada Tenaga Kerja Indonesia pendamping (WNI).',
            'status' => 'active',
        ]);
    }

    /**
     * Check permits expiring within given days for Kemnaker & Imigrasi renewal
     */
    public function checkExpiringPermits($arg1 = 60, $arg2 = 60): int|Collection
    {
        if (is_numeric($arg1) && func_num_args() === 1) {
            $daysThreshold = (int) $arg1;
            $tenantId = null;
        } elseif (is_numeric($arg1) && is_numeric($arg2)) {
            $tenantId = (int) $arg1;
            $daysThreshold = (int) $arg2;
        } else {
            $daysThreshold = 60;
            $tenantId = null;
        }

        $today = Carbon::today();
        $targetDate = $today->copy()->addDays($daysThreshold);

        $query = ExpatriatePermit::where('status', 'active')
            ->whereBetween('rptka_expiry_date', [$today->toDateString(), $targetDate->toDateString()]);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $permits = $query->get();

        foreach ($permits as $permit) {
            $permit->update(['status' => 'expiring_soon']);
        }

        return $permits->count();
    }

    /**
     * Generate Kemnaker TKA report payload
     */
    public function generateKemnakerReportPayload(Company $company): array
    {
        $permits = ExpatriatePermit::where('company_id', $company->id)->get();

        return [
            'perusahaan' => $company->name,
            'total_tka' => $permits->count(),
            'tka_list' => $permits->toArray(),
        ];
    }

    /**
     * Update permit operational status
     */
    public function updatePermitStatus(ExpatriatePermit $permit, string $status): ExpatriatePermit
    {
        $permit->update(['status' => $status]);
        return $permit->fresh();
    }
}
