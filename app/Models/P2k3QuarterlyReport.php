<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2k3QuarterlyReport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'p2k3_committee_id',
        'reporting_year',
        'quarter',
        'workplace_inspections_count',
        'safety_trainings_count',
        'incident_summary',
        'disnaker_submission_date',
        'disnaker_receipt_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reporting_year' => 'integer',
            'workplace_inspections_count' => 'integer',
            'safety_trainings_count' => 'integer',
            'disnaker_submission_date' => 'date',
        ];
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(P2k3Committee::class, 'p2k3_committee_id');
    }
}
