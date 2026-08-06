<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\ApprovalChain;
use App\Domain\Core\Models\ApprovalRequest;
use App\Domain\Core\Models\ApprovalHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Exception;

class ApprovalService
{
    /**
     * Submit a model instance for approval.
     */
    public function submit(Model $approvable, User $requester, ?float $amount = null, ?string $notes = null): ?ApprovalRequest
    {
        $modelType = get_class($approvable);
        $module = strtolower(explode('\\', $modelType)[2] ?? 'core');

        // Find applicable approval chain based on module, model_type and amount
        $chainQuery = ApprovalChain::where('model_type', $modelType)
            ->where('is_active', true);

        if ($amount !== null) {
            $chainQuery->where('min_amount', '<=', $amount)
                ->where(function ($q) use ($amount) {
                    $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
                });
        }

        $chain = $chainQuery->with('steps')->first();

        if (!$chain || $chain->steps->isEmpty()) {
            // No chain required or configured; auto-approve if applicable
            return null;
        }

        return ApprovalRequest::create([
            'approval_chain_id' => $chain->id,
            'approvable_type'   => $modelType,
            'approvable_uuid'   => $approvable->uuid ?? $approvable->id,
            'requester_id'      => $requester->id,
            'current_step_order'=> 1,
            'status'            => 'pending',
            'notes'             => $notes,
        ]);
    }

    /**
     * Approve the current step in the approval request.
     */
    public function approve(ApprovalRequest $request, User $approver, ?string $comments = null): ApprovalRequest
    {
        if ($request->status !== 'pending') {
            throw new Exception("Approval request is already in status: {$request->status}");
        }

        $currentStep = $request->chain->steps()
            ->where('step_order', $request->current_step_order)
            ->first();

        // Record history log
        ApprovalHistory::create([
            'approval_request_id' => $request->id,
            'step_order'          => $request->current_step_order,
            'approver_id'         => $approver->id,
            'action'              => 'approved',
            'comments'            => $comments,
        ]);

        if (!$currentStep || $currentStep->is_final_step) {
            $request->update(['status' => 'approved']);
            
            // Optionally update target model status if method exists
            $approvable = $request->approvable;
            if ($approvable && method_exists($approvable, 'onApprovalComplete')) {
                $approvable->onApprovalComplete();
            }
        } else {
            $request->increment('current_step_order');
        }

        return $request->fresh(['histories', 'chain.steps']);
    }

    /**
     * Reject the approval request.
     */
    public function reject(ApprovalRequest $request, User $approver, ?string $reason = null): ApprovalRequest
    {
        if ($request->status !== 'pending') {
            throw new Exception("Approval request is already in status: {$request->status}");
        }

        ApprovalHistory::create([
            'approval_request_id' => $request->id,
            'step_order'          => $request->current_step_order,
            'approver_id'         => $approver->id,
            'action'              => 'rejected',
            'comments'            => $reason,
        ]);

        $request->update(['status' => 'rejected']);

        $approvable = $request->approvable;
        if ($approvable && method_exists($approvable, 'onApprovalRejected')) {
            $approvable->onApprovalRejected($reason);
        }

        return $request->fresh(['histories']);
    }
}
