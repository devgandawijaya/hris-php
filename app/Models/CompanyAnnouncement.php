<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAnnouncement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'content',
        'category', // general, policy, holiday, event, urgent
        'target_department_id',
        'is_pinned',
        'published_at',
        'expires_at',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'published_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function targetDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'target_department_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
