<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Shift\StoreShiftRequest;
use App\Http\Requests\Platform\HRM\Shift\UpdateShiftRequest;
use App\Models\HRM\Shift;
use App\Services\HRM\ShiftService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Shifts", description: "API Endpoints for Shift Management")]
class ShiftController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/shifts",
        summary: "List all shifts",
        security: [["sanctum" => []]],
        tags: ["HRM Shifts"],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $perPage = request()->input('per_page', 15);
        $shifts = $this->shiftService->getAllShifts($perPage);
        return response()->json([
            'message' => 'List of shifts',
            'data' => $shifts
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/shifts",
        summary: "Create a new shift",
        security: [["sanctum" => []]],
        tags: ["HRM Shifts"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["name", "start_time", "end_time"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "start_time", type: "string", format: "time", example: "09:00"),
                        new OA\Property(property: "end_time", type: "string", format: "time", example: "17:00"),
                        new OA\Property(property: "description", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Shift created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreShiftRequest $request): JsonResponse
    {
        $shift = $this->shiftService->createShift($request->validated());
        return response()->json([
            'message' => 'Shift created successfully',
            'data' => $shift
        ], 201);
    }

    #[OA\Get(
        path: "/api/platform/hrm/shifts/{uuid}",
        summary: "Get shift details",
        security: [["sanctum" => []]],
        tags: ["HRM Shifts"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Shift not found")
        ]
    )]
    public function show($uuid): JsonResponse
    {
        $shift = $this->shiftService->findShift($uuid);
        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }
        return response()->json([
            'message' => 'Shift details',
            'data' => $shift
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/shifts/{uuid}",
        summary: "Update a shift",
        security: [["sanctum" => []]],
        tags: ["HRM Shifts"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["name", "start_time", "end_time"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "start_time", type: "string", format: "time"),
                        new OA\Property(property: "end_time", type: "string", format: "time"),
                        new OA\Property(property: "description", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Shift updated"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 404, description: "Shift not found")
        ]
    )]
    public function update(UpdateShiftRequest $request, $uuid): JsonResponse
    {
        $shift = $this->shiftService->findShift($uuid);
        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }
        $updatedShift = $this->shiftService->updateShift($shift, $request->validated());
        return response()->json([
            'message' => 'Shift updated successfully',
            'data' => $updatedShift
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/shifts/{uuid}",
        summary: "Delete a shift",
        security: [["sanctum" => []]],
        tags: ["HRM Shifts"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Shift deleted"),
            new OA\Response(response: 404, description: "Shift not found")
        ]
    )]
    public function destroy($uuid): JsonResponse
    {
        $shift = $this->shiftService->findShift($uuid);
        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }
        $this->shiftService->deleteShift($shift);
        return response()->json([
            'message' => 'Shift deleted successfully'
        ]);
    }
}
