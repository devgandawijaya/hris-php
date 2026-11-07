<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutsourcingVendor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'vendor_code',
        'name',
        'legal_entity_type',
        'nib_oss_license',
        'disnaker_registration_number',
        'pic_name',
        'pic_contact',
        'management_fee_percentage',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'management_fee_percentage' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(OutsourcingContract::class);
    }
}
