<?php

namespace App\Services\EmployeeRelations;

use App\Models\CompanyAnnouncement;
use App\Models\Employee;
use App\Models\HrHelpdeskTicket;
use App\Models\User;
use Illuminate\Support\Carbon;

class EngagementService
{
    /**
     * Create an official corporate announcement broadcast.
     */
    public function createAnnouncement(int $tenantId, array $data, User $creator): CompanyAnnouncement
    {
        return CompanyAnnouncement::create([
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'content' => $data['content'],
            'category' => $data['category'] ?? 'general',
            'target_department_id' => $data['target_department_id'] ?? null,
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'published_at' => $data['published_at'] ?? now()->toDateString(),
            'expires_at' => $data['expires_at'] ?? null,
            'created_by_user_id' => $creator->id,
        ]);
    }

    /**
     * Submit an HR Helpdesk service request from Employee Self-Service.
     */
    public function createTicket(Employee $employee, array $data): HrHelpdeskTicket
    {
        $seq = str_pad(HrHelpdeskTicket::where('tenant_id', $employee->tenant_id)->count() + 1, 4, '0', STR_PAD_LEFT);
        $ticketNo = "TICK-" . date('Ym') . "-{$seq}";

        return HrHelpdeskTicket::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'ticket_number' => $ticketNo,
            'category' => $data['category'] ?? 'general',
            'subject' => $data['subject'],
            'description' => $data['description'],
            'status' => 'open',
        ]);
    }

    /**
     * Resolve and fulfill an HR Helpdesk ticket.
     */
    public function resolveTicket(HrHelpdeskTicket $ticket, User $resolver, string $resolutionNotes): HrHelpdeskTicket
    {
        $ticket->update([
            'status' => 'resolved',
            'assigned_to_user_id' => $resolver->id,
            'resolution_notes' => $resolutionNotes,
            'resolved_at' => Carbon::now(),
        ]);

        return $ticket->fresh();
    }
}

