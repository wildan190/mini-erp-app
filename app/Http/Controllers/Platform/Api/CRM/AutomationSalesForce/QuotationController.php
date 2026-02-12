<?php

namespace App\Http\Controllers\Platform\Api\CRM\AutomationSalesForce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\AutomationSalesForce\QuotationRequest;
use App\Services\CRM\QuotationService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Quotations", description: "API Endpoints for Quotation Management")]
class QuotationController extends Controller
{
    #[OA\Get(
        path: "/api/platform/crm/quotation",
        summary: "List all quotations",
        security: [["sanctum" => []]],
        tags: ["Quotations"],
        responses: [
            new OA\Response(response: 200, description: "List of quotations")
        ]
    )]
    public function index(QuotationService $service)
    {
        return response()->json([
            'message' => 'List quotation',
            'data' => $service->index()
        ]);
    }

    #[OA\Get(
        path: "/api/platform/crm/quotation/{uuid}",
        summary: "Get quotation detail",
        security: [["sanctum" => []]],
        tags: ["Quotations"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Quotation detail")
        ]
    )]
    public function show($id, QuotationService $service)
    {
        return response()->json([
            'message' => 'Detail quotation',
            'data' => $service->show($id)
        ]);
    }

    #[OA\Post(
        path: "/api/platform/crm/quotation",
        summary: "Create new quotation with items",
        security: [["sanctum" => []]],
        tags: ["Quotations"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["customer_id"],
                    properties: [
                        new OA\Property(property: "customer_id", type: "string", description: "UUID or ID of customer"),
                        new OA\Property(property: "valid_until", type: "string", format: "date", example: "2026-12-31"),
                        new OA\Property(property: "discount_amount", type: "number"),
                        new OA\Property(property: "terms", type: "string"),
                        new OA\Property(property: "items[0][description]", type: "string", example: "Consultation"),
                        new OA\Property(property: "items[0][quantity]", type: "number", example: 1),
                        new OA\Property(property: "items[0][unit_price]", type: "number", example: 1000000),
                        new OA\Property(property: "items[0][tax_rate]", type: "number", example: 11)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Quotation created")
        ]
    )]
    public function store(QuotationRequest $request, QuotationService $service)
    {
        $quotation = $service->create($request->validated());

        return response()->json([
            'message' => 'Quotation berhasil dibuat',
            'data' => $quotation
        ], 201);
    }

    #[OA\Put(
        path: "/api/platform/crm/quotation/{uuid}",
        summary: "Update quotation",
        security: [["sanctum" => []]],
        tags: ["Quotations"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "status", type: "string", enum: ["draft", "sent", "accepted", "declined", "expired"])
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Quotation updated")
        ]
    )]
    public function update($id, QuotationRequest $request, QuotationService $service)
    {
        $quotation = $service->update($id, $request->validated());

        return response()->json([
            'message' => 'Quotation berhasil diperbarui',
            'data' => $quotation
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/crm/quotation/{uuid}",
        summary: "Delete quotation",
        security: [["sanctum" => []]],
        tags: ["Quotations"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Quotation deleted")
        ]
    )]
    public function destroy($id, QuotationService $service)
    {
        $service->delete($id);

        return response()->json([
            'message' => 'Quotation berhasil dihapus'
        ]);
    }

    public function print($id, QuotationService $service)
    {
        $quotation = $service->show($id);
        return view('crm.quotation.print', compact('quotation'));
    }
}