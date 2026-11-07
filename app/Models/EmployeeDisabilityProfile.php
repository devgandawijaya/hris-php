<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDisabilityProfile extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'disability_category', // physical, sensory_vision, sensory_hearing, intellectual, mental_psychosocial, multiple
        'medical_certificate_number',
        'accommodations_provided',
        'accessibility_needs',
        'emergency_evacuation_assistance_plan',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
