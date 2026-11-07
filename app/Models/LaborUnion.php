<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaborUnion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'union_name',
        'registration_number',
        'chairperson_name',
        'total_verified_members',
        'workforce_percentage',
        'has_bargaining_rights',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_verified_members' => 'integer',
            'workforce_percentage' => 'decimal:2',
            'has_bargaining_rights' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function pkbNegotiations(): HasMany
    {
        return $this->hasMany(PkbNegotiation::class);
    }

    public function duesDeductions(): HasMany
    {
        return $this->hasMany(UnionDuesDeduction::class);
    }
}
