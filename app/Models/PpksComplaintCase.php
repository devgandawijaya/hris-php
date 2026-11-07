<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpksComplaintCase extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'ticket_number',
        'incident_date',
        'allegation_type',
        'is_anonymous',
        'complainant_pseudonym',
        'investigator_satgas_lead',
        'victim_support_measures',
        'sanction_recommendation',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'is_anonymous' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
