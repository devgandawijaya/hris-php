<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PkbNegotiation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'labor_union_id',
        'pkb_period_years',
        'draft_code',
        'negotiation_start_date',
        'negotiation_target_end_date',
        'employer_delegation_lead',
        'union_delegation_lead',
        'status',
        'disnaker_decree_number',
        'summary_clauses',
    ];

    protected function casts(): array
    {
        return [
            'negotiation_start_date' => 'date',
            'negotiation_target_end_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function laborUnion(): BelongsTo
    {
        return $this->belongsTo(LaborUnion::class);
    }
}
