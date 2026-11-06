<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Models\Workflow;
use App\Services\Workflow\WorkflowEngineService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkflowManagementController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected WorkflowEngineService $workflowService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();

        $workflows = Workflow::with('steps')
            ->where('tenant_id', $tenant->id)
            ->get();

        $pendingApprovals = ApprovalRequest::with(['workflow', 'approvable', 'histories'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        $roles = Role::all();

        return view('company.workflows.index', compact('workflows', 'pendingApprovals', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'entity_type' => 'required|string|in:leave,overtime,reimbursement,payroll',
            'approver_role_slugs' => 'required|array|min:1',
            'approver_role_slugs.*' => 'required|string',
        ]);

        $this->workflowService->createWorkflow(
            $tenant->id,
            $validated['name'],
            $validated['entity_type'],
            $validated['approver_role_slugs']
        );

        return back()->with('success', "Alur kerja persetujuan '{$validated['name']}' berhasil dibuat.");
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        $this->workflowService->approve($approvalRequest, Auth::user(), $validated['comment'] ?? null);

        return back()->with('success', "Persetujuan langkah alur kerja berhasil dicatat.");
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->workflowService->reject($approvalRequest, Auth::user(), $validated['reason']);

        return back()->with('success', "Permohonan berhasil ditolak.");
    }
}

