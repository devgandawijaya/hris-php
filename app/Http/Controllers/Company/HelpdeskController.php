<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\HrHelpdeskTicket;
use App\Services\EmployeeRelations\EngagementService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HelpdeskController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected EngagementService $engagementService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');

        $query = HrHelpdeskTicket::with(['employee.department', 'assignedTo'])
            ->where('tenant_id', $tenant->id)
            ->latest();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(15);

        $stats = [
            'total' => HrHelpdeskTicket::where('tenant_id', $tenant->id)->count(),
            'open' => HrHelpdeskTicket::where('tenant_id', $tenant->id)->where('status', 'open')->count(),
            'resolved' => HrHelpdeskTicket::where('tenant_id', $tenant->id)->where('status', 'resolved')->count(),
        ];

        return view('company.helpdesk.index', compact('tickets', 'stats'));
    }

    public function resolve(Request $request, HrHelpdeskTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $this->engagementService->resolveTicket($ticket, Auth::user(), $validated['resolution_notes']);

        return back()->with('success', "Tiket layanan {$ticket->ticket_number} berhasil diselesaikan.");
    }
}
