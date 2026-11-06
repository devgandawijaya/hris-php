<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reimbursement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'category', // medical, transport, meal, business_trip, operational, other
        'title',
        'description',
        'amount',
        'expense_date',
        'receipt_path',
        'status', // pending, approved_manager, approved_finance, paid, rejected
        'payroll_id',
        'reviewed_by_manager_id',
        'reviewed_by_finance_id',
        'rejection_reason',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function managerReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_manager_id');
    }

    public function financeReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_finance_id');
    }
}
