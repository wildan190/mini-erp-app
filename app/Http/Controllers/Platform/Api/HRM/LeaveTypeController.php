<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Models\HRM\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Leave Types", description: "API Endpoints for Leave Types")]
class LeaveTypeController extends Controller
{
    #[OA\Get(
        path: "/api/platform/hrm/leave-types",
        summary: "List all leave types",
        security: [["sanctum" => []]],
        tags: ["HRM Leave Types"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $types = LeaveType::all();
        return response()->json([
            'message' => 'List of leave types',
            'data' => $types
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/leave-types",
        summary: "Create a new leave type",
        security: [["sanctum" => []]],
        tags: ["HRM Leave Types"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["name", "days_allowed"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "days_allowed", type: "integer"),
                        new OA\Property(property: "description", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Leave type created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:leave_types,name',
            'days_allowed' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $type = LeaveType::create($validated);
        return response()->json([
            'message' => 'Leave type created successfully',
            'data' => $type
        ], 201);
    }
}
