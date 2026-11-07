<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IjpVacancy extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'department_id',
        'position_id',
        'vacancy_code',
        'title',
        'description',
        'requirements',
        'min_tenure_months',
        'min_performance_score',
        'open_slots',
        'application_deadline',
        'status', // open, closed, cancelled
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'application_deadline' => 'date',
            'min_performance_score' => 'decimal:2',
            'min_tenure_months' => 'integer',
            'open_slots' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IjpApplication::class);
    }
}
