<?php

namespace App\Services\Workflow;

use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkflowEngineService
{
    /**
     * Create or update a workflow definition with ordered steps.
     */
    public function createWorkflow(int $tenantId, string $name, string $entityType, array $approverRoleSlugs): Workflow
    {
        return DB::transaction(function () use ($tenantId, $name, $entityType, $approverRoleSlugs) {
            $workflow = Workflow::create([
                'tenant_id' => $tenantId,
                'name' => $name,
                'entity_type' => $entityType,
                'is_active' => true,
            ]);

            foreach ($approverRoleSlugs as $index => $roleSlug) {
                WorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_order' => $index + 1,
                    'approver_role_slug' => $roleSlug,
                ]);
            }

            return $workflow;
        });
    }

    /**
     * Initiate an approval process for an approvable entity (Leave, Reimbursement, Overtime, Payroll).
     */
    public function initiateApproval(Model $entity, string $entityType): ?ApprovalRequest
    {
        $tenantId = $entity->tenant_id ?? null;
        if (!$tenantId) {
            return null;
        }

        $workflow = Workflow::where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->where('is_active', true)
            ->first();

        if (!$workflow || $workflow->steps->isEmpty()) {
            return null;
        }

        return ApprovalRequest::create([
            'tenant_id' => $tenantId,
            'workflow_id' => $workflow->id,
            'approvable_type' => get_class($entity),
            'approvable_id' => $entity->id,
            'current_step' => 1,
            'status' => 'pending',
        ]);
    }

    /**
     * Approve the current step in the workflow.
     */
    public function approve(ApprovalRequest $request, User $approver, ?string $comment = null): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $approver, $comment) {
            ApprovalHistory::create([
                'approval_request_id' => $request->id,
                'approver_id' => $approver->id,
                'action' => 'approved',
                'comments' => $comment,
            ]);

            $totalSteps = $request->workflow->steps()->count();

            if ($request->current_step >= $totalSteps) {
                // Final Step Reached -> Finalized
                $request->update(['status' => 'approved']);

                // Auto update underlying approvable model status if attribute exists
                $approvable = $request->approvable;
                if ($approvable && in_array('status', $approvable->getFillable())) {
                    $approvable->update(['status' => 'approved']);
                }
            } else {
                // Move to next step
                $request->increment('current_step');
            }

            return $request->fresh();
        });
    }

    /**
     * Reject the approval request.
     */
    public function reject(ApprovalRequest $request, User $approver, string $reason): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $approver, $reason) {
            ApprovalHistory::create([
                'approval_request_id' => $request->id,
                'approver_id' => $approver->id,
                'action' => 'rejected',
                'comments' => $reason,
            ]);

            $request->update(['status' => 'rejected']);

            $approvable = $request->approvable;
            if ($approvable && in_array('status', $approvable->getFillable())) {
                $approvable->update(['status' => 'rejected']);
            }

            return $request->fresh();
        });
    }

    /**
     * Delegate the approval step to another authorized user.
     */
    public function delegate(ApprovalRequest $request, User $delegator, User $delegatee, ?string $comment = null): ApprovalRequest
    {
        ApprovalHistory::create([
            'approval_request_id' => $request->id,
            'approver_id' => $delegator->id,
            'action' => 'delegated',
            'comments' => "Didelegasikan kepada {$delegatee->name}: " . ($comment ?? ''),
        ]);

        return $request->fresh();
    }
}
