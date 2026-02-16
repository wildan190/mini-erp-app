<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\LeadTrackingRequest;
use App\Models\CRM\Lead;
use App\Services\CRM\LeadService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Leads", description: "API Endpoints for Lead Management")]
class LeadTrackingController extends Controller
{
    #[OA\Get(
        path: "/api/platform/crm/leads",
        summary: "List all leads",
        security: [["sanctum" => []]],
        tags: ["Leads"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(LeadService $service)
    {
        return response()->json([
            'message' => 'List lead',
            'data' => $service->index()
        ]);
    }

    #[OA\Get(
        path: "/api/platform/crm/leads/{uuid}",
        summary: "Get lead detail",
        security: [["sanctum" => []]],
        tags: ["Leads"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lead detail")
        ]
    )]
    public function show($id, LeadService $service)
    {
        return response()->json([
            'message' => 'Detail lead',
            'data' => $service->show($id)
        ]);
    }

    #[OA\Post(
        path: "/api/platform/crm/leads",
        summary: "Create new lead",
        security: [["sanctum" => []]],
        tags: ["Leads"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["lead_name", "source"],
                    properties: [
                        new OA\Property(property: "lead_name", type: "string", example: "Alice Smith"),
                        new OA\Property(property: "email", type: "string", example: "alice@example.com"),
                        new OA\Property(property: "phone", type: "string"),
                        new OA\Property(property: "company", type: "string"),
                        new OA\Property(property: "source", type: "string", example: "Website"),
                        new OA\Property(property: "notes", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Lead created")
        ]
    )]
    public function store(LeadTrackingRequest $request, LeadService $service)
    {
        $lead = $service->create($request->validated());

        return response()->json([
            'message' => 'Lead berhasil disimpan',
            'data' => $lead
        ], 201);
    }

    #[OA\Put(
        path: "/api/platform/crm/leads/{uuid}",
        summary: "Update existing lead",
        security: [["sanctum" => []]],
        tags: ["Leads"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "lead_name", type: "string"),
                        new OA\Property(property: "status", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Lead updated")
        ]
    )]
    public function update($id, LeadTrackingRequest $request, LeadService $service)
    {
        $lead = $service->update($id, $request->validated());

        return response()->json([
            'message' => 'Lead berhasil diperbarui',
            'data' => $lead
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/crm/leads/{uuid}",
        summary: "Delete lead",
        security: [["sanctum" => []]],
        tags: ["Leads"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lead deleted")
        ]
    )]
    public function destroy($id, LeadService $service)
    {
        $service->delete($id);

        return response()->json([
            'message' => 'Lead berhasil dihapus'
        ]);
    }

    #[OA\Post(
        path: "/api/platform/crm/leads/{uuid}/convert",
        summary: "Convert lead to prospect",
        security: [["sanctum" => []]],
        tags: ["Leads"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lead converted")
        ]
    )]
    public function convert($uuid, LeadService $service)
    {
        $lead = $service->show($uuid);
        $prospect = $service->convertToProspect($lead);

        return response()->json([
            'message' => 'Lead berhasil dikonversi ke Prospect',
            'data' => $prospect->load('customer')
        ]);
    }
}