<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHandoverItem extends Model
{
    protected $fillable = [
        'asset_handover_bast_id',
        'company_asset_id',
        'asset_name',
        'serial_number',
        'condition_on_return', // good, minor_damage, severe_damage, lost
        'compensation_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'compensation_amount' => 'decimal:2',
        ];
    }

    public function bast(): BelongsTo
    {
        return $this->belongsTo(AssetHandoverBast::class, 'asset_handover_bast_id');
    }

    public function companyAsset(): BelongsTo
    {
        return $this->belongsTo(CompanyAsset::class);
    }
}
