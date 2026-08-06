<?php

namespace App\Domain\Core\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Core\Models\ApprovalChain;
use App\Domain\Core\Models\ApprovalRequest;
use App\Domain\Core\Services\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Approval Engine", description: "Multi-tier approval workflow management")]
class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    public function indexChains(): JsonResponse
    {
        $chains = ApprovalChain::with('steps')->get();
        return response()->json(['success' => true, 'data' => $chains]);
    }

    public function storeChain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'module'     => 'required|string',
            'model_type' => 'required|string',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'steps'      => 'required|array|min:1',
            'steps.*.step_order'    => 'required|integer',
            'steps.*.approver_type' => 'required|in:role,user,department_manager',
            'steps.*.approver_uuid' => 'nullable|string',
            'steps.*.is_final_step' => 'boolean',
        ]);

        $chain = ApprovalChain::create([
            'name'       => $validated['name'],
            'module'     => $validated['module'],
            'model_type' => $validated['model_type'],
            'min_amount' => $validated['min_amount'] ?? 0,
            'max_amount' => $validated['max_amount'] ?? null,
        ]);

        foreach ($validated['steps'] as $stepData) {
            $chain->steps()->create($stepData);
        }

        return response()->json(['success' => true, 'data' => $chain->load('steps')], 201);
    }

    public function indexPendingRequests(Request $request): JsonResponse
    {
        $requests = ApprovalRequest::with(['chain', 'requester', 'histories.approver'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $requests]);
    }

    public function approve(Request $request, string $uuid): JsonResponse
    {
        $approvalReq = ApprovalRequest::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['comments' => 'nullable|string']);

        $updated = $this->approvalService->approve($approvalReq, $request->user(), $validated['comments'] ?? null);

        return response()->json(['success' => true, 'message' => 'Request approved', 'data' => $updated]);
    }

    public function reject(Request $request, string $uuid): JsonResponse
    {
        $approvalReq = ApprovalRequest::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['reason' => 'required|string']);

        $updated = $this->approvalService->reject($approvalReq, $request->user(), $validated['reason']);

        return response()->json(['success' => true, 'message' => 'Request rejected', 'data' => $updated]);
    }
}
