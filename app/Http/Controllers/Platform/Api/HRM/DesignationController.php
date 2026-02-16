<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Designation\StoreDesignationRequest;
use App\Http\Requests\Platform\HRM\Designation\UpdateDesignationRequest;
use App\Services\HRM\DesignationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Designations", description: "API Endpoints for Designation Management")]
class DesignationController extends Controller
{
    protected DesignationService $designationService;

    public function __construct(DesignationService $designationService)
    {
        $this->designationService = $designationService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/designations",
        summary: "List all designations",
        security: [["sanctum" => []]],
        tags: ["HRM Designations"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'List of designations',
            'data' => $this->designationService->getAllDesignations()
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/designations",
        summary: "Create a new designation",
        security: [["sanctum" => []]],
        tags: ["HRM Designations"],
        responses: [
            new OA\Response(response: 201, description: "Designation created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreDesignationRequest $request): JsonResponse
    {
        $designation = $this->designationService->createDesignation($request->validated());
        return response()->json([
            'message' => 'Designation created successfully',
            'data' => $designation
        ], 201);
    }

    #[OA\Get(
        path: "/api/platform/hrm/designations/{id}",
        summary: "Get designation details",
        security: [["sanctum" => []]],
        tags: ["HRM Designations"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Designation not found")
        ]
    )]
    public function show($id): JsonResponse
    {
        $designation = \App\Models\HRM\Designation::findOrFail($id);
        return response()->json([
            'message' => 'Designation details',
            'data' => $designation
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/designations/{id}",
        summary: "Update a designation",
        security: [["sanctum" => []]],
        tags: ["HRM Designations"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Designation updated"),
            new OA\Response(response: 404, description: "Designation not found")
        ]
    )]
    public function update(UpdateDesignationRequest $request, $id): JsonResponse
    {
        $designation = \App\Models\HRM\Designation::findOrFail($id);
        $updatedDesignation = $this->designationService->updateDesignation($designation, $request->validated());
        return response()->json([
            'message' => 'Designation updated successfully',
            'data' => $updatedDesignation
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/designations/{id}",
        summary: "Delete a designation",
        security: [["sanctum" => []]],
        tags: ["HRM Designations"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Designation deleted"),
            new OA\Response(response: 404, description: "Designation not found")
        ]
    )]
    public function destroy($id): JsonResponse
    {
        $designation = \App\Models\HRM\Designation::findOrFail($id);
        $this->designationService->deleteDesignation($designation);
        return response()->json([
            'message' => 'Designation deleted successfully'
        ]);
    }
}
