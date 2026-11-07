<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FemaleWorkerFacilityCheck extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'inspection_date',
        'lactation_room_standard_met',
        'night_shift_transport_provided',
        'night_shift_nutritious_food_provided',
        'daycare_subsidy_available',
        'compliance_notes',
        'audited_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'lactation_room_standard_met' => 'boolean',
            'night_shift_transport_provided' => 'boolean',
            'night_shift_nutritious_food_provided' => 'boolean',
            'daycare_subsidy_available' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function auditedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by_user_id');
    }
}
