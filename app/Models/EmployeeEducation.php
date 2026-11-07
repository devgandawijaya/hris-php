<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEducation extends Model
{
    use BelongsToTenant;

    protected $table = 'employee_educations';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'degree_level',
        'institution_name',
        'major',
        'graduation_year',
        'gpa',
        'certificate_number',
    ];

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'gpa' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
