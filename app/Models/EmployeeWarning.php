<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWarning extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'warning_level', // sp1, sp2, sp3
        'letter_number',
        'issued_date',
        'valid_until',
        'reason',
        'action_plan',
        'authorized_by_user_id',
        'status', // active, expired, revoked
        'file_path',
        'level',
        'sp_number',
        'warning_letter_number',
    ];

    public function setWarningLetterNumberAttribute($value): void
    {
        $this->attributes['letter_number'] = $value;
    }

    public function getWarningLetterNumberAttribute(): ?string
    {
        return $this->letter_number;
    }

    public function setLevelAttribute($value): void
    {
        $this->attributes['warning_level'] = is_numeric($value) ? "sp{$value}" : (string)$value;
    }

    public function getLevelAttribute(): ?string
    {
        return $this->warning_level;
    }

    public function setSpNumberAttribute($value): void
    {
        $this->attributes['letter_number'] = $value;
    }

    public function getSpNumberAttribute(): ?string
    {
        return $this->letter_number;
    }

    protected static function booted(): void
    {
        static::creating(function (EmployeeWarning $w) {
            if (empty($w->warning_level)) {
                $w->warning_level = 'sp1';
            }
            if (empty($w->letter_number)) {
                $w->letter_number = $w->warning_letter_number ?? ('SP/' . date('Ymd') . '/' . uniqid());
            }
        });
    }

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }

    public function isExpired(): bool
    {
        return $this->valid_until->isPast();
    }
}

