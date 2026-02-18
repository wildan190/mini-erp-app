<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Leave\StoreLeaveRequestRequest;
use App\Http\Requests\Platform\HRM\Leave\UpdateLeaveStatusRequest;
use App\Models\HRM\LeaveRequest;
use App\Services\HRM\LeaveService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Leave Requests", description: "API Endpoints for Leave Requests")]
class LeaveRequestController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/leave-requests",
        summary: "List leave requests",
        security: [["sanctum" => []]],
        tags: ["HRM Leave Requests"],
        parameters: [
            new OA\Parameter(name: "employee_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["pending", "approved", "rejected"])),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $filters = request()->only(['employee_id', 'status']);
        $perPage = request()->input('per_page', 15);
        $requests = $this->leaveService->getLeaveRequests($filters, $perPage);
        return response()->json([
            'message' => 'List of leave requests',
            'data' => $requests
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/leave-requests",
        summary: "Apply for leave",
        security: [["sanctum" => []]],
        tags: ["HRM Leave Requests"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["leave_type_id", "start_date", "end_date", "reason"],
                    properties: [
                        new OA\Property(property: "leave_type_id", type: "integer"),
                        new OA\Property(property: "start_date", type: "string", format: "date"),
                        new OA\Property(property: "end_date", type: "string", format: "date"),
                        new OA\Property(property: "reason", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Leave applied successfully"),
            new OA\Response(response: 400, description: "Error (e.g., limit exceeded)"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->employee) {
            return response()->json(['message' => 'User is not an employee'], 404);
        }

        try {
            $leaveRequest = $this->leaveService->applyForLeave($user->employee, $request->validated());
            return response()->json([
                'message' => 'Leave applied successfully',
                'data' => $leaveRequest
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    #[OA\Put(
        path: "/api/platform/hrm/leave-requests/{uuid}/status",
        summary: "Approve or Reject leave request",
        security: [["sanctum" => []]],
        tags: ["HRM Leave Requests"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["status"],
                    properties: [
                        new OA\Property(property: "status", type: "string", enum: ["approved", "rejected"]),
                        new OA\Property(property: "rejection_reason", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Status updated"),
            new OA\Response(response: 400, description: "Error")
        ]
    )]
    public function updateStatus(UpdateLeaveStatusRequest $request, $uuid): JsonResponse
    {
        $leaveRequest = $this->leaveService->findLeaveRequest($uuid);
        if (!$leaveRequest) {
            return response()->json(['message' => 'Leave request not found'], 404);
        }

        try {
            $updatedRequest = $this->leaveService->updateLeaveStatus(
                $leaveRequest,
                $request->status,
                auth()->id(),
                $request->rejection_reason
            );

            return response()->json([
                'message' => 'Leave request ' . $request->status,
                'data' => $updatedRequest
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: "/api/platform/hrm/leave-balances/my-balance",
        summary: "Get my leave balance",
        security: [["sanctum" => []]],
        tags: ["HRM Leave Requests"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function myBalance(): JsonResponse
    {
        $user = auth()->user();
        if (!$user->employee) {
            return response()->json(['message' => 'User is not an employee'], 404);
        }

        $balance = $this->leaveService->getEmployeeBalance($user->employee->id, now()->year);
        return response()->json([
            'message' => 'Your leave balance',
            'data' => $balance
        ]);
    }
}
