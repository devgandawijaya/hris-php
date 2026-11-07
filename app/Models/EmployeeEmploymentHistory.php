<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEmploymentHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'employee_employment_histories';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'company_name',
        'position_title',
        'start_date',
        'end_date',
        'last_salary',
        'leaving_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'last_salary' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
