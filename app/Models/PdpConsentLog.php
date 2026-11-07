<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdpConsentLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'consent_type',
        'purpose_description',
        'ip_address',
        'user_agent',
        'consented_at',
        'revoked_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'revoked_at' => 'datetime',
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
}
