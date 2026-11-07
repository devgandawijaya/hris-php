<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeShiftRosterAssignment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'continuous_shift_roster_id',
        'employee_id',
        'group_name', // Grup A, Grup B, Grup C, Grup D
        'effective_start_date',
        'effective_end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'effective_start_date' => 'date',
            'effective_end_date' => 'date',
        ];
    }

    public function roster(): BelongsTo
    {
        return $this->belongsTo(ContinuousShiftRoster::class, 'continuous_shift_roster_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
