<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoluntarySeparationProgram extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'program_code',
        'title',
        'open_date',
        'close_date',
        'severance_multiplier',
        'additional_golden_handshake_bonus',
        'max_quota',
        'approved_applicants_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'open_date' => 'date',
            'close_date' => 'date',
            'severance_multiplier' => 'decimal:2',
            'additional_golden_handshake_bonus' => 'decimal:2',
            'max_quota' => 'integer',
            'approved_applicants_count' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(VssApplication::class);
    }
}
