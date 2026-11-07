<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsgMetricSnapshot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'reporting_year',
        'gender_pay_gap_ratio',
        'female_workforce_pct',
        'female_leadership_pct',
        'board_diversity_pct',
        'avg_training_hours_per_employee',
        'annual_turnover_rate',
        'lost_time_injury_frequency_rate',
        'status',
        'sustainability_notes',
    ];

    protected function casts(): array
    {
        return [
            'reporting_year' => 'integer',
            'gender_pay_gap_ratio' => 'decimal:4',
            'female_workforce_pct' => 'decimal:2',
            'female_leadership_pct' => 'decimal:2',
            'board_diversity_pct' => 'decimal:2',
            'avg_training_hours_per_employee' => 'decimal:2',
            'annual_turnover_rate' => 'decimal:2',
            'lost_time_injury_frequency_rate' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
