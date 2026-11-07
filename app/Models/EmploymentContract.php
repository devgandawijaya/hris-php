<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentContract extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'contract_number',
        'type', // pkwt, pkwtt, internship, probation
        'start_date',
        'end_date',
        'probation_end_date',
        'monthly_wage',
        'status', // active, expiring_soon, expired, renewed, terminated
        'compensation_calculated',
        'compensation_paid',
        'renewal_count',
        'document_path',
        'notes',
        'contract_body',
        'employee_signature',
        'employee_signed_at',
        'employee_sign_ip',
        'company_signature',
        'company_signed_at',
        'company_sign_ip',
        'signer_name',
        'signer_position',
        'signed_hash',
        'is_digitally_signed',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'probation_end_date' => 'date',
            'monthly_wage' => 'decimal:2',
            'compensation_calculated' => 'decimal:2',
            'compensation_paid' => 'boolean',
            'renewal_count' => 'integer',
            'employee_signed_at' => 'datetime',
            'company_signed_at' => 'datetime',
            'is_digitally_signed' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getContractTypeAttribute(): string
    {
        return strtoupper($this->type ?? 'PKWT');
    }

    public function getDocumentIntegrityHashAttribute(): ?string
    {
        return $this->signed_hash;
    }

    public function getCompanySignerNameAttribute(): ?string
    {
        return $this->signer_name;
    }

    public function getCompanySignerTitleAttribute(): ?string
    {
        return $this->signer_position;
    }

    public function getCompanySignerIpAttribute(): ?string
    {
        return $this->company_sign_ip;
    }

    public function getEmployeeSignerIpAttribute(): ?string
    {
        return $this->employee_sign_ip;
    }

    public function getBasicSalaryAttribute(): float
    {
        return (float) ($this->monthly_wage ?? 0);
    }

    public function getAllowancesAttribute(): float
    {
        return 0.00;
    }
}

