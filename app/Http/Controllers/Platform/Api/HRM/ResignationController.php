<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Resignation\StoreResignationRequest;
use App\Http\Requests\Platform\HRM\Resignation\UpdateResignationStatusRequest;
use App\Models\HRM\Employee;
use App\Models\HRM\Resignation;
use App\Services\HRM\ResignationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Resignations", description: "API Endpoints for Resignation & Offboarding")]
class ResignationController extends Controller
{
    protected ResignationService $resignationService;

    public function __construct(ResignationService $resignationService)
    {
        $this->resignationService = $resignationService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/resignations",
        summary: "List resignations",
        security: [["sanctum" => []]],
        tags: ["HRM Resignations"],
        parameters: [
            new OA\Parameter(name: "employee_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["pending", "approved", "rejected", "withdrawn"])),
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
        $resignations = $this->resignationService->getResignations($filters, $perPage);
        return response()->json([
            'message' => 'List of resignations',
            'data' => $resignations
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/resignations",
        summary: "Submit a resignation request",
        security: [["sanctum" => []]],
        tags: ["HRM Resignations"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["notice_date", "resignation_date", "reason"],
                    properties: [
                        new OA\Property(property: "notice_date", type: "string", format: "date"),
                        new OA\Property(property: "resignation_date", type: "string", format: "date"),
                        new OA\Property(property: "reason", type: "string"),
                        new OA\Property(property: "handover_to", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Resignation submitted"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreResignationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }
        $data['employee_id'] = $employee->id;

        $resignation = $this->resignationService->submitResignation($data);

        return response()->json([
            'message' => 'Resignation submitted successfully',
            'data' => $resignation
        ], 201);
    }

    #[OA\Get(
        path: "/api/platform/hrm/resignations/{uuid}",
        summary: "Get resignation details",
        security: [["sanctum" => []]],
        tags: ["HRM Resignations"],
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
        $resignation = $this->resignationService->findResignation($uuid);
        if (!$resignation) {
            return response()->json(['message' => 'Resignation not found'], 404);
        }
        return response()->json([
            'message' => 'Resignation details',
            'data' => $resignation
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/resignations/{uuid}/status",
        summary: "Update resignation status",
        security: [["sanctum" => []]],
        tags: ["HRM Resignations"],
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
                        new OA\Property(property: "status", type: "string", enum: ["approved", "rejected", "withdrawn"]),
                        new OA\Property(property: "remarks", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Status updated"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function updateStatus(UpdateResignationStatusRequest $request, $uuid): JsonResponse
    {
        $resignation = $this->resignationService->findResignation($uuid);
        if (!$resignation) {
            return response()->json(['message' => 'Resignation not found'], 404);
        }
        $status = $request->status;
        $remarks = $request->remarks;

        $updatedResignation = $this->resignationService->updateStatus($resignation, $status, $remarks);

        return response()->json([
            'message' => 'Resignation status updated',
            'data' => $updatedResignation
        ]);
    }
}
