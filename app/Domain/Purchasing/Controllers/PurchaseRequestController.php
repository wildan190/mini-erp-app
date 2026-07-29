<?php

namespace App\Domain\Purchasing\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Purchasing\Models\PurchaseRequest;
use App\Domain\Purchasing\Models\PurchaseRequestItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Purchasing - Purchase Requests", description: "Internal purchase request management")]
class PurchaseRequestController extends Controller
{
    #[OA\Get(
        path: "/api/platform/purchasing/requests",
        summary: "List purchase requests",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Requests"],
        parameters: [
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["pending", "approved", "rejected"])),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [new OA\Response(response: 200, description: "List of purchase requests")]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseRequest::with(['requestor', 'items'])->orderBy('created_at', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        return response()->json(['message' => 'List of purchase requests', 'data' => $query->paginate($request->input('per_page', 15))]);
    }

    #[OA\Post(
        path: "/api/platform/purchasing/requests",
        summary: "Create purchase request",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Requests"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["date", "items"],
                properties: [
                    new OA\Property(property: "date", type: "string", format: "date"),
                    new OA\Property(property: "department_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "notes", type: "string"),
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "item_name", type: "string"),
                            new OA\Property(property: "qty", type: "number"),
                            new OA\Property(property: "notes", type: "string"),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Purchase request created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date'                => 'required|date',
            'department_uuid'     => 'nullable|string',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.item_name'   => 'required|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.notes'       => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $pr = PurchaseRequest::create([
                'number'          => 'PR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'date'            => $validated['date'],
                'requestor_id'    => $request->user()->id,
                'department_uuid' => $validated['department_uuid'] ?? null,
                'notes'           => $validated['notes'] ?? null,
                'status'          => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $pr->items()->create($item);
            }

            return response()->json(['message' => 'Purchase request created successfully', 'data' => $pr->load('items')], 201);
        });
    }

    #[OA\Get(
        path: "/api/platform/purchasing/requests/{uuid}",
        summary: "Get purchase request detail",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Requests"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 200, description: "Purchase request detail")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $pr = PurchaseRequest::with(['requestor', 'items'])->where('uuid', $uuid)->firstOrFail();
        return response()->json(['message' => 'Purchase request detail', 'data' => $pr]);
    }

    #[OA\Patch(
        path: "/api/platform/purchasing/requests/{uuid}/status",
        summary: "Update purchase request status",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Requests"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [new OA\Property(property: "status", type: "string", enum: ["pending", "approved", "rejected"])]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Status updated")]
    )]
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $pr = PurchaseRequest::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['status' => 'required|in:pending,approved,rejected']);
        $pr->update(['status' => $validated['status']]);
        return response()->json(['message' => 'Purchase request status updated', 'data' => $pr]);
    }
}
