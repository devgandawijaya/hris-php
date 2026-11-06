<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeSurvey extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'survey_type',
        'start_date',
        'end_date',
        'is_anonymous',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_anonymous' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EmployeeSurveyResponse::class);
    }

    /**
     * Calculate eNPS metric score (-100 to +100)
     */
    public function calculateEnps(): array
    {
        $total = $this->responses()->whereNotNull('enps_score')->count();
        if ($total === 0) {
            return [
                'total_responses' => 0,
                'promoters' => 0,
                'passives' => 0,
                'detractors' => 0,
                'enps_score' => 0,
            ];
        }

        $promoters = $this->responses()->where('enps_score', '>=', 9)->count();
        $passives = $this->responses()->whereBetween('enps_score', [7, 8])->count();
        $detractors = $this->responses()->where('enps_score', '<=', 6)->count();

        $enpsScore = round((($promoters - $detractors) / $total) * 100, 1);

        return [
            'total_responses' => $total,
            'promoters' => $promoters,
            'passives' => $passives,
            'detractors' => $detractors,
            'enps_score' => $enpsScore,
        ];
    }
}

