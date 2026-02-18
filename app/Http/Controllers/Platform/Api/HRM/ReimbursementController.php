<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Reimbursement\StoreReimbursementRequest;
use App\Http\Requests\Platform\HRM\Reimbursement\UpdateReimbursementStatusRequest;
use App\Models\HRM\Employee;
use App\Models\HRM\Reimbursement;
use App\Services\HRM\ReimbursementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Reimbursements", description: "API Endpoints for Claims & Reimbursements")]
class ReimbursementController extends Controller
{
    protected ReimbursementService $reimbursementService;

    public function __construct(ReimbursementService $reimbursementService)
    {
        $this->reimbursementService = $reimbursementService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/reimbursements",
        summary: "List reimbursements",
        security: [["sanctum" => []]],
        tags: ["HRM Reimbursements"],
        parameters: [
            new OA\Parameter(name: "employee_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["pending", "approved", "rejected", "paid"])),
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
        $reimbursements = $this->reimbursementService->getReimbursements($filters, $perPage);
        return response()->json([
            'message' => 'List of reimbursements',
            'data' => $reimbursements
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/reimbursements",
        summary: "Submit a reimbursement claim",
        security: [["sanctum" => []]],
        tags: ["HRM Reimbursements"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["type", "amount"],
                    properties: [
                        new OA\Property(property: "type", type: "string"),
                        new OA\Property(property: "amount", type: "number"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "proof_file", type: "string", format: "binary"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Claim submitted"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreReimbursementRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Auto-assign employee_id based on logged-in user
        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found for this user.'], 404);
        }
        $data['employee_id'] = $employee->id;

        $reimbursement = $this->reimbursementService->submitClaim($data);

        return response()->json([
            'message' => 'Reimbursement claim submitted successfully',
            'data' => $reimbursement
        ], 201);
    }

    #[OA\Get(
        path: "/api/platform/hrm/reimbursements/{uuid}",
        summary: "Get reimbursement details",
        security: [["sanctum" => []]],
        tags: ["HRM Reimbursements"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function show($uuid): JsonResponse
    {
        $reimbursement = $this->reimbursementService->findReimbursement($uuid);
        if (!$reimbursement) {
            return response()->json(['message' => 'Reimbursement not found'], 404);
        }
        return response()->json([
            'message' => 'Reimbursement details',
            'data' => $reimbursement
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/reimbursements/{uuid}/status",
        summary: "Update reimbursement status",
        security: [["sanctum" => []]],
        tags: ["HRM Reimbursements"],
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
                        new OA\Property(property: "status", type: "string", enum: ["approved", "rejected", "paid"]),
                        new OA\Property(property: "reason", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Status updated"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function updateStatus(UpdateReimbursementStatusRequest $request, $uuid): JsonResponse
    {
        $reimbursement = $this->reimbursementService->findReimbursement($uuid);
        if (!$reimbursement) {
            return response()->json(['message' => 'Reimbursement not found'], 404);
        }
        $status = $request->status;
        $reason = $request->reason;

        $updatedReimbursement = $this->reimbursementService->updateStatus(
            $reimbursement,
            $status,
            Auth::id(),
            $reason
        );

        return response()->json([
            'message' => 'Reimbursement status updated',
            'data' => $updatedReimbursement
        ]);
    }

    #[OA\Get(
        path: "/api/platform/hrm/reimbursements/my-claims",
        summary: "Get my reimbursement claims",
        security: [["sanctum" => []]],
        tags: ["HRM Reimbursements"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function myClaims(): JsonResponse
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        $claims = $this->reimbursementService->getReimbursements(['employee_id' => $employee->id]);

        return response()->json([
            'message' => 'My reimbursement claims',
            'data' => $claims
        ]);
    }
}
