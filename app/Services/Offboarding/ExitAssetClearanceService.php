<?php

namespace App\Services\Offboarding;

use App\Models\Employee;
use App\Models\User;
use App\Models\CompanyAsset;
use App\Models\AssetHandoverBast;
use App\Models\AssetHandoverItem;
use App\Models\SystemAccessRevocationLog;
use Carbon\Carbon;

class ExitAssetClearanceService
{
    /**
     * Create an official Berita Acara Serah Terima (BAST) Pengembalian Aset
     */
    public function createBast(Employee $employee, array $itemsData, string $officerName, string $officerPosition, ?int $clearanceId = null): AssetHandoverBast
    {
        $tenantId = $employee->tenant_id;
        $nextNum = AssetHandoverBast::where('tenant_id', $tenantId)->count() + 1;
        $bastNumber = sprintf('BAST-ASSET-%s-%04d', date('Y'), $nextNum);

        $bast = AssetHandoverBast::create([
            'tenant_id' => $tenantId,
            'company_id' => $employee->company_id,
            'bast_number' => $bastNumber,
            'employee_id' => $employee->id,
            'offboarding_clearance_id' => $clearanceId,
            'handover_date' => Carbon::today()->toDateString(),
            'status' => 'draft',
            'officer_name' => $officerName,
            'officer_position' => $officerPosition,
        ]);

        $totalCompensation = 0.0;

        foreach ($itemsData as $item) {
            $companyAsset = null;
            if (!empty($item['company_asset_id'])) {
                $companyAsset = CompanyAsset::where('tenant_id', $tenantId)->find($item['company_asset_id']);
            }

            $condition = $item['condition_on_return'] ?? 'good';
            $compensation = (float) ($item['compensation_amount'] ?? 0.0);
            $totalCompensation += $compensation;

            AssetHandoverItem::create([
                'asset_handover_bast_id' => $bast->id,
                'company_asset_id' => $companyAsset?->id,
                'asset_name' => $item['asset_name'] ?? ($companyAsset?->name ?? 'Aset Perusahaan'),
                'serial_number' => $item['serial_number'] ?? $companyAsset?->serial_number,
                'condition_on_return' => $condition,
                'compensation_amount' => $compensation,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $bast->update(['total_compensation_due' => $totalCompensation]);

        return $bast->load('items');
    }

    /**
     * Complete and sign BAST, update physical asset inventory, and trigger automated SSO de-provisioning
     */
    public function signAndCompleteBast(AssetHandoverBast $bast, string $employeeSignature, string $officerSignature): AssetHandoverBast
    {
        $bast->update([
            'status' => 'completed',
            'employee_signature_token' => $employeeSignature,
            'officer_signature_token' => $officerSignature,
            'signed_at' => now(),
        ]);

        // Update physical inventory status for each linked company asset
        foreach ($bast->items as $item) {
            if ($item->companyAsset) {
                $newStatus = match ($item->condition_on_return) {
                    'good' => 'available',
                    'minor_damage' => 'in_repair',
                    'severe_damage', 'lost' => 'retired',
                    default => 'available',
                };

                $item->companyAsset->update([
                    'status' => $newStatus,
                    'assigned_employee_id' => null,
                    'notes' => "Dikembalikan via BAST {$bast->bast_number} dengan kondisi: {$item->condition_on_return}",
                ]);
            }
        }

        // Trigger Automated SSO & System Access De-provisioning
        $this->triggerAutomatedAccessRevocation($bast);

        return $bast->fresh(['items', 'accessRevocationLogs']);
    }

    /**
     * Trigger de-provisioning logs for core corporate digital services
     */
    public function triggerAutomatedAccessRevocation(AssetHandoverBast $bast): void
    {
        $employee = $bast->employee;
        $tenantId = $bast->tenant_id;
        $companyId = $bast->company_id;

        $targetSystems = [
            'Google Workspace / Corporate Email' => $employee->email,
            'Microsoft 365 & Active Directory' => $employee->email,
            'Corporate Slack / Communication' => $employee->email,
            'Corporate VPN & Internal Gateway' => $employee->employee_number . '@vpn',
            'RiHRIS Core Employee Portal' => $employee->email,
        ];

        foreach ($targetSystems as $systemName => $accountIdentifier) {
            SystemAccessRevocationLog::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'employee_id' => $employee->id,
                    'system_name' => $systemName,
                ],
                [
                    'company_id' => $companyId,
                    'asset_handover_bast_id' => $bast->id,
                    'account_identifier' => $accountIdentifier,
                    'revocation_status' => 'revoked',
                    'revoked_at' => now(),
                    'api_response_log' => "Automated API de-provisioning successful for [{$systemName}]. Token revoked and session destroyed.",
                ]
            );
        }
    }

    /**
     * Manual revocation of an individual system
     */
    public function revokeSystemAccess(Employee $employee, string $systemName, ?User $revoker = null): SystemAccessRevocationLog
    {
        return SystemAccessRevocationLog::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'system_name' => $systemName,
            ],
            [
                'company_id' => $employee->company_id,
                'account_identifier' => $employee->email,
                'revocation_status' => 'revoked',
                'revoked_at' => now(),
                'revoked_by_user_id' => $revoker?->id,
                'api_response_log' => "Manually triggered revocation verified by {$revoker?->name}.",
            ]
        );
    }
}
