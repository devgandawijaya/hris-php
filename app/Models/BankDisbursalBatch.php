<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankDisbursalBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payroll_period_id',
        'batch_number',
        'bank_target',
        'total_records',
        'total_amount',
        'file_format',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'total_records' => 'integer',
            'total_amount' => 'decimal:2',
        ];
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
