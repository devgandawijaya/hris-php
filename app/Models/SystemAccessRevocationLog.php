<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAccessRevocationLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'asset_handover_bast_id',
        'system_name', // Google Workspace, Slack, VPN, M365, HRIS
        'account_identifier',
        'revocation_status', // pending, revoked, failed
        'revoked_at',
        'revoked_by_user_id',
        'api_response_log',
    ];

    protected function casts(): array
    {
        return [
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

    public function bast(): BelongsTo
    {
        return $this->belongsTo(AssetHandoverBast::class, 'asset_handover_bast_id');
    }

    public function revokedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }
}
