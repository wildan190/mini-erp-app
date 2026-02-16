<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\ProspectRequest;
use App\Http\Requests\Platform\CRM\ProspectManagement\ProspectStatusRequest;
use App\Models\CRM\Prospect;
use App\Services\CRM\ProspectService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Prospects", description: "API Endpoints for Prospect Management")]
class ProspectController extends Controller
{
    #[OA\Get(
        path: "/api/platform/crm/prospects",
        summary: "List all prospects",
        security: [["sanctum" => []]],
        tags: ["Prospects"],
        responses: [
            new OA\Response(response: 200, description: "List of prospects")
        ]
    )]
    public function index(ProspectService $service)
    {
        return response()->json([
            'message' => 'List prospect',
            'data' => $service->index()
        ]);
    }

    #[OA\Get(
        path: "/api/platform/crm/prospects/{uuid}",
        summary: "Get prospect detail",
        security: [["sanctum" => []]],
        tags: ["Prospects"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Prospect detail")
        ]
    )]
    public function show($id, ProspectService $service)
    {
        return response()->json([
            'message' => 'Detail prospect',
            'data' => $service->show($id)
        ]);
    }

    #[OA\Post(
        path: "/api/platform/crm/prospects",
        summary: "Create new prospect",
        security: [["sanctum" => []]],
        tags: ["Prospects"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["customer_id", "title", "status"],
                    properties: [
                        new OA\Property(property: "customer_id", type: "string", description: "UUID or ID of customer"),
                        new OA\Property(property: "title", type: "string", example: "New Project"),
                        new OA\Property(
                            property: "status",
                            type: "string",
                            enum: ["new", "qualified", "proposal", "negotiation", "closed_won", "closed_lost"],
                            example: "negotiation"
                        ),
                        new OA\Property(property: "expected_value", type: "number"),
                        new OA\Property(property: "expected_closing_date", type: "string", format: "date"),
                        new OA\Property(property: "probability", type: "integer"),
                        new OA\Property(property: "notes", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Prospect created")
        ]
    )]
    public function store(ProspectRequest $request, ProspectService $service)
    {
        $prospect = $service->create($request->validated());

        return response()->json([
            'message' => 'Prospect berhasil dibuat',
            'data' => $prospect
        ], 201);
    }

    #[OA\Put(
        path: "/api/platform/crm/prospects/{uuid}",
        summary: "Update prospect",
        security: [["sanctum" => []]],
        tags: ["Prospects"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(
                            property: "status",
                            type: "string",
                            enum: ["new", "qualified", "proposal", "negotiation", "closed_won", "closed_lost"]
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Prospect updated")
        ]
    )]
    public function update($id, ProspectRequest $request, ProspectService $service)
    {
        $prospect = $service->update($id, $request->validated());

        return response()->json([
            'message' => 'Prospect berhasil diperbarui',
            'data' => $prospect
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/crm/prospects/{uuid}",
        summary: "Delete prospect",
        security: [["sanctum" => []]],
        tags: ["Prospects"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Prospect deleted")
        ]
    )]
    public function destroy($id, ProspectService $service)
    {
        $service->delete($id);

        return response()->json([
            'message' => 'Prospect berhasil dihapus'
        ]);
    }

    #[OA\Put(
        path: "/api/platform/crm/prospects/{uuid}/status",
        summary: "Update prospect status and log to pipeline",
        security: [["sanctum" => []]],
        tags: ["Prospects"],
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
                        new OA\Property(
                            property: "status",
                            type: "string",
                            enum: ["new", "qualified", "proposal", "negotiation", "closed_won", "closed_lost"],
                            example: "closed_won"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Status updated")
        ]
    )]
    public function updateStatus(ProspectStatusRequest $request, $uuid, ProspectService $service)
    {
        $prospect = $service->show($uuid);
        $service->updateStatus($prospect, $request->status);

        return response()->json([
            'message' => 'Status prospect berhasil diperbarui',
            'data' => $prospect->load('pipelines')
        ]);
    }
}