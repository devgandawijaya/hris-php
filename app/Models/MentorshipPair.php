<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorshipPair extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'mentee_employee_id',
        'mentor_employee_id',
        'start_date',
        'end_date',
        'status', // active, completed, terminated
        'program_objectives',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentee_employee_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentor_employee_id');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(MentorshipCheckin::class);
    }
}
