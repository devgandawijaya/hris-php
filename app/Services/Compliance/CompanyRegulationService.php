<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\CompanyRegulation;
use Carbon\Carbon;

class CompanyRegulationService
{
    /**
     * Register Company Regulation (PP) or Collective Labor Agreement (PKB).
     */
    public function registerRegulation(Company $company, array $data): CompanyRegulation
    {
        $startDate = Carbon::parse($data['effective_start_date']);
        $endDate = Carbon::parse($data['effective_end_date']);

        // UU 13/2003 Pasal 111 ayat 3: Masa berlaku PP maksimal 2 tahun
        $maxAllowedEnd = $startDate->copy()->addYears(2);
        if ($endDate->gt($maxAllowedEnd)) {
            $endDate = $maxAllowedEnd;
        }

        return CompanyRegulation::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'document_type' => $data['document_type'] ?? 'peraturan_perusahaan_pp',
            'title' => $data['title'],
            'reg_number' => $data['reg_number'] ?? null,
            'effective_start_date' => $startDate->toDateString(),
            'effective_end_date' => $endDate->toDateString(),
            'disnaker_office' => $data['disnaker_office'] ?? null,
            'status' => 'active',
            'summary_provisions' => $data['summary_provisions'] ?? null,
        ]);
    }

    /**
     * Check and flag regulations nearing expiry (within 90 days / H-90).
     */
    public function getRenewalAlerts(Company $company): array
    {
        $now = now();
        $regulations = CompanyRegulation::where('company_id', $company->id)->get();

        $alerts = [];
        foreach ($regulations as $reg) {
            $endDate = Carbon::parse($reg->effective_end_date);
            $daysLeft = $now->diffInDays($endDate, false);

            if ($daysLeft <= 0) {
                $status = 'expired';
                $reg->update(['status' => 'expired']);
            } elseif ($daysLeft <= 90) {
                $status = 'renewal_pending';
                $reg->update(['status' => 'renewal_pending']);
            } else {
                $status = 'active';
            }

            $alerts[] = [
                'regulation_id' => $reg->id,
                'title' => $reg->title,
                'document_type' => $reg->document_type,
                'effective_end_date' => $reg->effective_end_date->toDateString(),
                'days_remaining' => (int) $daysLeft,
                'status' => $status,
                'needs_action' => $daysLeft <= 90,
            ];
        }

        return $alerts;
    }
}
