<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThrRecord extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'year',
        'months_of_service',
        'base_wage',
        'thr_amount',
        'payment_date',
        'status', // draft, approved, paid
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'months_of_service' => 'integer',
            'base_wage' => 'decimal:2',
            'thr_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

