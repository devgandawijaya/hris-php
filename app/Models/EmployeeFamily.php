<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamily extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'name',
        'relationship',
        'gender',
        'birth_date',
        'nik',
        'education_level',
        'is_bpjs_covered',
        'is_tax_dependent',
        'contact_number',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_bpjs_covered' => 'boolean',
            'is_tax_dependent' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
