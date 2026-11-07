<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\BelongsToTenant;

class EmployeeMcuRecord extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'mcu_date',
        'clinic_provider',
        'blood_pressure',
        'cholesterol',
        'blood_sugar',
        'fitness_category',
        'doctor_notes',
        'follow_up_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'mcu_date' => 'date',
            'cholesterol' => 'decimal:2',
            'blood_sugar' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
