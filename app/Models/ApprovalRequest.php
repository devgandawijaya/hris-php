<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'approvable_type',
        'approvable_id',
        'current_step',
        'status', // pending, approved, rejected
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class)->latest();
    }

    public function currentWorkflowStep(): ?WorkflowStep
    {
        return $this->workflow?->steps()->where('step_order', $this->current_step)->first();
    }
}
