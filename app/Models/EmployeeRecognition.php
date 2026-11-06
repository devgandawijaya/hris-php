<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRecognition extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sender_user_id',
        'employee_id',
        'badge_type',
        'title',
        'message',
        'reward_amount',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'decimal:2',
            'is_public' => 'boolean',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

