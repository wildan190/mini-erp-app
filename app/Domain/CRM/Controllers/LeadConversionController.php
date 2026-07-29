<?php

namespace App\Domain\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\CRM\Models\Lead;
use App\Domain\CRM\Models\Prospect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "CRM - Lead Conversion", description: "Convert leads to prospects and customers")]
class LeadConversionController extends Controller
{
    #[OA\Post(
        path: "/api/platform/crm/leads/convert-to-prospect",
        summary: "Convert a lead to prospect",
        security: [["sanctum" => []]],
        tags: ["CRM - Lead Conversion"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["lead_uuid", "title", "expected_value"],
                properties: [
                    new OA\Property(property: "lead_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "title", type: "string", example: "ERP Implementation Q4"),
                    new OA\Property(property: "expected_value", type: "number"),
                    new OA\Property(property: "expected_close_date", type: "string", format: "date"),
                    new OA\Property(property: "notes", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Lead converted to prospect"),
            new OA\Response(response: 404, description: "Lead not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function convertToProspect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_uuid'           => 'required|string|exists:leads,uuid',
            'title'               => 'required|string|max:255',
            'expected_value'      => 'required|numeric|min:0',
            'expected_close_date' => 'nullable|date|after:today',
            'notes'               => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $lead = Lead::where('uuid', $validated['lead_uuid'])->firstOrFail();

            $prospect = Prospect::create([
                'title'               => $validated['title'],
                'expected_value'      => $validated['expected_value'],
                'expected_close_date' => $validated['expected_close_date'] ?? null,
                'notes'               => $validated['notes'] ?? null,
                'status'              => 'new',
                'source_lead_uuid'    => $lead->uuid,
            ]);

            $lead->update(['status' => 'converted']);

            return response()->json([
                'message' => 'Lead successfully converted to prospect',
                'data'    => $prospect,
            ], 201);
        });
    }
}
