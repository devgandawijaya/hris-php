<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnionDuesDeduction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'labor_union_id',
        'employee_id',
        'monthly_dues_amount',
        'consent_letter_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_dues_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function laborUnion(): BelongsTo
    {
        return $this->belongsTo(LaborUnion::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
