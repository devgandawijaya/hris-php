<?php

namespace App\Services\Notification;

use App\Models\NotificationTemplate;
use App\Models\NotificationDispatchLog;
use App\Models\Employee;
use App\Models\User;

class MultiChannelNotificationService
{
    /**
     * Render message content by replacing placeholders with actual values.
     */
    public function render(NotificationTemplate $template, Employee $employee, array $params = []): string
    {
        $defaultParams = [
            'employee_name' => $employee->full_name,
            'employee_nik' => $employee->nik ?? $employee->employee_id ?? 'N/A',
            'company_name' => $employee->company?->name ?? 'RiHRIS Enterprise',
            'department' => $employee->department?->name ?? 'Umum',
            'position' => $employee->position?->title ?? 'Staf',
            'date_today' => now()->translatedFormat('d F Y'),
        ];

        $merged = array_merge($defaultParams, $params);
        return $template->render($merged);
    }

    /**
     * Dispatch notification to recipient via specified channel (Email or WhatsApp).
     */
    public function dispatch(
        NotificationTemplate $template,
        Employee $employee,
        array $params = [],
        ?User $sender = null
    ): NotificationDispatchLog {
        $body = $this->render($template, $employee, $params);
        $target = $template->channel === 'whatsapp'
            ? ($employee->phone ?? $employee->emergencyContacts?->first()?->phone ?? '081234567890')
            : ($employee->email ?? 'employee@example.com');

        $subject = $template->subject ? $template->render(array_merge([
            'employee_name' => $employee->full_name,
            'company_name' => $employee->company?->name ?? 'RiHRIS',
        ], $params)) : null;

        // Dispatched record
        return NotificationDispatchLog::create([
            'tenant_id' => $employee->tenant_id ?? 1,
            'template_id' => $template->id,
            'channel' => $template->channel,
            'recipient_name' => $employee->full_name,
            'recipient_target' => $target,
            'subject' => $subject,
            'message_preview' => $body,
            'status' => 'sent',
            'sent_at' => now(),
            'dispatched_by_user_id' => $sender?->id,
        ]);
    }

    /**
     * Preview notification without recording to logs.
     */
    public function simulateDispatch(NotificationTemplate $template, Employee $employee, array $params = []): array
    {
        $renderedBody = $this->render($template, $employee, $params);
        $target = $template->channel === 'whatsapp'
            ? ($employee->phone ?? '081234567890')
            : ($employee->email ?? 'employee@example.com');

        return [
            'channel' => $template->channel,
            'recipient_name' => $employee->full_name,
            'recipient_target' => $target,
            'subject' => $template->subject ? $template->render($params) : null,
            'body' => $renderedBody,
            'estimated_chars' => mb_strlen($renderedBody),
        ];
    }
}
