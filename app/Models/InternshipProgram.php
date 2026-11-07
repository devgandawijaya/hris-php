<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternshipProgram extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'program_title',
        'department_id',
        'start_date',
        'end_date',
        'pocket_money_allowance_daily',
        'bpjs_bpu_covered',
        'mentor_employee_id',
        'intern_candidate_name',
        'intern_nik_ktp',
        'institution_origin',
        'agreement_letter_number',
        'statutory_ratio_compliant',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'pocket_money_allowance_daily' => 'decimal:2',
            'bpjs_bpu_covered' => 'boolean',
            'statutory_ratio_compliant' => 'boolean',
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

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentor_employee_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(InternshipLogbook::class);
    }
}
