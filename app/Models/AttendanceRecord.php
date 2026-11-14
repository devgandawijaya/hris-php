<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_in_photo',
        'status',
        'late_minutes',
        'early_out_minutes',
        'work_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'clock_in_latitude' => 'decimal:7',
            'clock_in_longitude' => 'decimal:7',
            'late_minutes' => 'integer',
            'early_out_minutes' => 'integer',
            'work_hours' => 'decimal:2',
        ];
    }

    public function getDateAttribute($value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function setClockInAttribute($value): void
    {
        if ($value && strlen($value) === 5) {
            $value .= ':00';
        }
        $this->attributes['clock_in'] = $value;
    }

    public function setClockOutAttribute($value): void
    {
        if ($value && strlen($value) === 5) {
            $value .= ':00';
        }
        $this->attributes['clock_out'] = $value;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

