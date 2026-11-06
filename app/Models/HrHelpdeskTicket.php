<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrHelpdeskTicket extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'ticket_number',
        'category', // work_certificate, bank_reference, data_correction, bpjs_inquiry, general
        'subject',
        'description',
        'status', // open, in_progress, resolved, rejected
        'assigned_to_user_id',
        'resolution_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
