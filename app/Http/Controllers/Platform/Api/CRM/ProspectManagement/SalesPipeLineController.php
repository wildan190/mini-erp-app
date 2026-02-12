<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\SalesPipeLineRequest;
use App\Services\CRM\SalesPipelineService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Sales Pipeline", description: "API Endpoints for Sales Pipeline tracking")]
class SalesPipeLineController extends Controller
{
    #[OA\Get(
        path: "/api/platform/crm/sales-pipeline",
        summary: "List all pipeline transitions",
        security: [["sanctum" => []]],
        tags: ["Sales Pipeline"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(SalesPipelineService $service)
    {
        return response()->json([
            'message' => 'List sales pipeline',
            'data' => $service->index()
        ]);
    }

    #[OA\Get(
        path: "/api/platform/crm/sales-pipeline/{uuid}",
        summary: "Get pipeline transition detail",
        security: [["sanctum" => []]],
        tags: ["Sales Pipeline"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Pipeline detail")
        ]
    )]
    public function show($id, SalesPipelineService $service)
    {
        return response()->json([
            'message' => 'Detail sales pipeline',
            'data' => $service->show($id)
        ]);
    }

    #[OA\Post(
        path: "/api/platform/crm/sales-pipeline",
        summary: "Log new pipeline transition",
        security: [["sanctum" => []]],
        tags: ["Sales Pipeline"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["prospect_id", "stage"],
                    properties: [
                        new OA\Property(property: "prospect_id", type: "string", description: "UUID of the Prospect", example: "0194f4a9-8f0a-7b3b-967a-0a1b2c3d4e5f"),
                        new OA\Property(
                            property: "stage",
                            type: "string",
                            enum: ["new", "qualified", "proposal", "negotiation", "closed_won", "closed_lost"],
                            example: "qualified"
                        ),
                        new OA\Property(property: "notes", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Pipeline transition logged")
        ]
    )]
    public function store(SalesPipeLineRequest $request, SalesPipelineService $service)
    {
        $pipeline = $service->create($request->validated());

        return response()->json([
            'message' => 'Sales pipeline berhasil disimpan',
            'data' => $pipeline
        ], 201);
    }

    #[OA\Delete(
        path: "/api/platform/crm/sales-pipeline/{uuid}",
        summary: "Delete pipeline log entry",
        security: [["sanctum" => []]],
        tags: ["Sales Pipeline"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Entry deleted")
        ]
    )]
    public function destroy($id, SalesPipelineService $service)
    {
        $service->delete($id);

        return response()->json([
            'message' => 'Sales pipeline berhasil dihapus'
        ]);
    }
}