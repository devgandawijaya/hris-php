<?php

namespace App\Services\Contract;

use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Services\Compliance\PkwtCompensationCalculator;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function __construct(
        protected PkwtCompensationCalculator $compensationCalculator
    ) {}

    /**
     * Create an employment contract.
     */
    public function createContract(Employee $employee, array $data, ?UploadedFile $document = null): EmploymentContract
    {
        $docPath = null;
        if ($document) {
            $docPath = $document->store("tenants/{$employee->tenant_id}/contracts", 'public');
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = !empty($data['end_date']) ? Carbon::parse($data['end_date']) : null;
        $monthlyWage = (float) ($data['monthly_wage'] ?? $employee->basic_salary);

        // Calculate potential PKWT compensation if PKWT
        $compensation = 0.0;
        if (($data['type'] ?? 'pkwt') === 'pkwt' && $endDate) {
            $monthsOfService = $startDate->diffInMonths($endDate);
            $calc = $this->compensationCalculator->calculate($monthlyWage, $monthsOfService);
            $compensation = $calc['compensation_amount'];
        }

        return EmploymentContract::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'contract_number' => $data['contract_number'],
            'type' => $data['type'] ?? 'pkwt',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
            'probation_end_date' => !empty($data['probation_end_date']) ? Carbon::parse($data['probation_end_date'])->format('Y-m-d') : null,
            'monthly_wage' => $monthlyWage,
            'status' => 'active',
            'compensation_calculated' => $compensation,
            'compensation_paid' => false,
            'document_path' => $docPath,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Check contracts and flag expiring ones (e.g. <= 30 days).
     */
    public function updateExpiringContracts(int $tenantId, int $warningDays = 30): int
    {
        $today = Carbon::today();
        $warningDate = Carbon::today()->addDays($warningDays);

        $expiringContracts = EmploymentContract::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $warningDate)
            ->where('end_date', '>=', $today)
            ->get();

        foreach ($expiringContracts as $contract) {
            $contract->update(['status' => 'expiring_soon']);
        }

        // Also mark past due contracts as expired
        $expiredContracts = EmploymentContract::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'expiring_soon'])
            ->whereNotNull('end_date')
            ->where('end_date', '<', $today)
            ->get();

        foreach ($expiredContracts as $contract) {
            $contract->update(['status' => 'expired']);
        }

        return $expiringContracts->count();
    }

    /**
     * Renew a contract (e.g. extension of PKWT or transition to PKWTT).
     */
    public function renewContract(EmploymentContract $currentContract, array $newContractData, ?UploadedFile $document = null): EmploymentContract
    {
        return DB::transaction(function () use ($currentContract, $newContractData, $document) {
            // 1. Mark current contract as renewed
            $currentContract->update(['status' => 'renewed']);

            // 2. Create new contract with incremented renewal count
            $employee = $currentContract->employee;
            $newContractData['renewal_count'] = $currentContract->renewal_count + 1;

            return $this->createContract($employee, $newContractData, $document);
        });
    }

    /**
     * Mark PKWT compensation as paid.
     */
    public function markCompensationPaid(EmploymentContract $contract): void
    {
        $contract->update(['compensation_paid' => true]);
    }
}
