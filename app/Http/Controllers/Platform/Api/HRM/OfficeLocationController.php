<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\OfficeLocation\StoreOfficeLocationRequest;
use App\Http\Requests\Platform\HRM\OfficeLocation\UpdateOfficeLocationRequest;
use App\Models\HRM\OfficeLocation;
use App\Services\HRM\OfficeLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Office Locations", description: "API Endpoints for Office Location Management")]
class OfficeLocationController extends Controller
{
    protected OfficeLocationService $officeLocationService;

    public function __construct(OfficeLocationService $officeLocationService)
    {
        $this->officeLocationService = $officeLocationService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/office-locations",
        summary: "List office locations",
        security: [["sanctum" => []]],
        tags: ["HRM Office Locations"],
        parameters: [
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active']);
        $perPage = $request->input('per_page', 15);
        $locations = $this->officeLocationService->getOfficeLocations($filters, $perPage);

        return response()->json([
            'message' => 'List of office locations',
            'data' => $locations
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/office-locations",
        summary: "Create office location",
        security: [["sanctum" => []]],
        tags: ["HRM Office Locations"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["name", "address", "latitude", "longitude"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "address", type: "string"),
                        new OA\Property(property: "latitude", type: "number"),
                        new OA\Property(property: "longitude", type: "number"),
                        new OA\Property(property: "radius", type: "integer"),
                        new OA\Property(property: "is_active", type: "boolean"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Office location created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreOfficeLocationRequest $request): JsonResponse
    {
        $location = $this->officeLocationService->createOfficeLocation($request->validated());

        return response()->json([
            'message' => 'Office location created successfully',
            'data' => $location
        ], 201);
    }

    #[OA\Get(
        path: "/api/platform/hrm/office-locations/{id}",
        summary: "Get office location details",
        security: [["sanctum" => []]],
        tags: ["HRM Office Locations"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function show($id): JsonResponse
    {
        $location = OfficeLocation::findOrFail($id);
        return response()->json([
            'message' => 'Office location details',
            'data' => $location
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/office-locations/{id}",
        summary: "Update office location",
        security: [["sanctum" => []]],
        tags: ["HRM Office Locations"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "address", type: "string"),
                        new OA\Property(property: "latitude", type: "number"),
                        new OA\Property(property: "longitude", type: "number"),
                        new OA\Property(property: "radius", type: "integer"),
                        new OA\Property(property: "is_active", type: "boolean"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Office location updated"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function update(UpdateOfficeLocationRequest $request, $id): JsonResponse
    {
        $location = OfficeLocation::findOrFail($id);
        $updatedLocation = $this->officeLocationService->updateOfficeLocation($location, $request->validated());

        return response()->json([
            'message' => 'Office location updated successfully',
            'data' => $updatedLocation
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/office-locations/{id}",
        summary: "Delete office location",
        security: [["sanctum" => []]],
        tags: ["HRM Office Locations"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Office location deleted"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function destroy($id): JsonResponse
    {
        $location = OfficeLocation::findOrFail($id);
        $this->officeLocationService->deleteOfficeLocation($location);

        return response()->json([
            'message' => 'Office location deleted successfully'
        ]);
    }
}
