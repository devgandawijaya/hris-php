<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'loan_number',
        'loan_type',
        'type',
        'amount',
        'interest_rate',
        'term_months',
        'monthly_installment',
        'total_payable',
        'remaining_amount',
        'installment_amount',
        'installments_count',
        'status',
        'reason',
        'approved_by_user_id',
        'approved_at',
        'disbursed_at',
    ];

    public function setTypeAttribute($value): void
    {
        $this->attributes['loan_type'] = $value;
    }

    public function getTypeAttribute(): ?string
    {
        return $this->loan_type;
    }

    public function setInstallmentAmountAttribute($value): void
    {
        $this->attributes['monthly_installment'] = $value;
    }

    public function getInstallmentAmountAttribute(): ?float
    {
        return isset($this->attributes['monthly_installment']) ? (float) $this->attributes['monthly_installment'] : null;
    }

    public function setInstallmentsCountAttribute($value): void
    {
        $this->attributes['term_months'] = $value;
    }

    public function getInstallmentsCountAttribute(): ?int
    {
        return isset($this->attributes['term_months']) ? (int) $this->attributes['term_months'] : null;
    }

    protected static function booted(): void
    {
        static::creating(function (EmployeeLoan $loan) {
            if (empty($loan->loan_type)) {
                $loan->loan_type = $loan->attributes['type'] ?? 'emergency';
            }
            if (empty($loan->term_months)) {
                $loan->term_months = (int) ($loan->attributes['installments_count'] ?? 1);
            }
            if (empty($loan->monthly_installment)) {
                $loan->monthly_installment = isset($loan->attributes['installment_amount'])
                    ? (float) $loan->attributes['installment_amount']
                    : ($loan->term_months > 0 ? round((float) $loan->amount / $loan->term_months, 2) : (float) $loan->amount);
            }
            if (empty($loan->total_payable)) {
                $loan->total_payable = (float) $loan->amount;
            }
            unset($loan->attributes['type'], $loan->attributes['installments_count'], $loan->attributes['installment_amount']);
        });
        static::saving(function (EmployeeLoan $loan) {
            if (isset($loan->attributes['type'])) {
                if (empty($loan->loan_type)) {
                    $loan->loan_type = $loan->attributes['type'];
                }
                unset($loan->attributes['type']);
            }
            unset($loan->attributes['installments_count'], $loan->attributes['installment_amount']);
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'term_months' => 'integer',
            'monthly_installment' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class)->orderBy('installment_number');
    }
}

