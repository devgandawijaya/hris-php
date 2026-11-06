<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAsset extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'asset_code',
        'name',
        'category', // laptop, phone, vehicle, access_card, other
        'serial_number',
        'status', // available, assigned, in_repair, retired
        'assigned_employee_id',
        'assigned_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
        ];
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }
}

