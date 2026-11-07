<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetHandoverBast extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'bast_number',
        'employee_id',
        'offboarding_clearance_id',
        'handover_date',
        'status', // draft, signed, completed
        'officer_name',
        'officer_position',
        'employee_signature_token',
        'officer_signature_token',
        'signed_at',
        'total_compensation_due',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
            'signed_at' => 'datetime',
            'total_compensation_due' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function offboardingClearance(): BelongsTo
    {
        return $this->belongsTo(OffboardingClearance::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssetHandoverItem::class);
    }

    public function accessRevocationLogs(): HasMany
    {
        return $this->hasMany(SystemAccessRevocationLog::class);
    }
}
