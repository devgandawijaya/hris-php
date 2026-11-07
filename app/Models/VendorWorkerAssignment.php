<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorWorkerAssignment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'outsourcing_contract_id',
        'worker_name',
        'nik_ktp',
        'assigned_role',
        'bpjs_tk_registered',
        'bpjs_kes_registered',
        'monthly_wage_paid',
        'wage_compliant_with_umk',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bpjs_tk_registered' => 'boolean',
            'bpjs_kes_registered' => 'boolean',
            'monthly_wage_paid' => 'decimal:2',
            'wage_compliant_with_umk' => 'boolean',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(OutsourcingContract::class, 'outsourcing_contract_id');
    }
}
