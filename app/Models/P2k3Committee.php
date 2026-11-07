<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2k3Committee extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'decree_number',
        'disnaker_ratification_number',
        'formation_date',
        'chairman_name',
        'secretary_ahli_k3_name',
        'ahli_k3_skp_number',
        'total_committee_members',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'formation_date' => 'date',
            'total_committee_members' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function quarterlyReports(): HasMany
    {
        return $this->hasMany(P2k3QuarterlyReport::class);
    }
}
