<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotificationTemplate;
use App\Models\NotificationDispatchLog;
use App\Models\Employee;
use App\Services\Notification\MultiChannelNotificationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationHubController extends Controller
{
    public function __construct(
        protected MultiChannelNotificationService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');

        $templates = NotificationTemplate::where(function ($q) use ($tenantId) {
            $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
        })->orderBy('code')->get();

        $logs = NotificationDispatchLog::with(['template', 'dispatchedBy'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->take(50)
            ->get();

        $stats = [
            'total_sent' => NotificationDispatchLog::where('tenant_id', $tenantId)->count(),
            'whatsapp_count' => NotificationDispatchLog::where('tenant_id', $tenantId)->where('channel', 'whatsapp')->count(),
            'email_count' => NotificationDispatchLog::where('tenant_id', $tenantId)->where('channel', 'email')->count(),
        ];

        return view('company.notifications.hub', compact('templates', 'logs', 'employees', 'stats'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'channel' => ['required', 'in:whatsapp,email'],
            'category' => ['required', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body_template' => ['required', 'string'],
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['is_active'] = true;

        NotificationTemplate::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => $validated['code'], 'channel' => $validated['channel']],
            $validated
        );

        return back()->with('success', "Template notifikasi [{$validated['code']}] berhasil disimpan.");
    }

    public function dispatchTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => ['required', 'exists:notification_templates,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'extra_title' => ['nullable', 'string'],
            'extra_amount' => ['nullable', 'string'],
            'extra_date' => ['nullable', 'string'],
        ]);

        $template = NotificationTemplate::findOrFail($validated['template_id']);
        $employee = Employee::findOrFail($validated['employee_id']);

        $params = [];
        if (!empty($validated['extra_title'])) $params['title'] = $validated['extra_title'];
        if (!empty($validated['extra_amount'])) $params['amount'] = $validated['extra_amount'];
        if (!empty($validated['extra_date'])) $params['event_date'] = $validated['extra_date'];

        $log = $this->notificationService->dispatch($template, $employee, $params, $request->user());

        $targetText = strtoupper($template->channel) . " ke {$log->recipient_name} ({$log->recipient_target})";
        return back()->with('success', "Notifikasi pengujian berhasil dikirimkan via {$targetText}.");
    }
}
