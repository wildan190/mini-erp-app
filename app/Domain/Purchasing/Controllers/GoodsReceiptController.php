<?php

namespace App\Domain\Purchasing\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Purchasing\Models\GoodsReceipt;
use App\Domain\Purchasing\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Purchasing - Goods Receipts", description: "Goods receipt (BAST) management")]
class GoodsReceiptController extends Controller
{
    #[OA\Get(
        path: "/api/platform/purchasing/goods-receipts",
        summary: "List goods receipts",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Goods Receipts"],
        parameters: [new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "List of goods receipts")]
    )]
    public function index(Request $request): JsonResponse
    {
        $receipts = GoodsReceipt::with(['order.supplier', 'items', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json(['message' => 'List of goods receipts', 'data' => $receipts]);
    }

    #[OA\Post(
        path: "/api/platform/purchasing/goods-receipts",
        summary: "Create goods receipt",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Goods Receipts"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["purchase_order_uuid", "date", "items"],
                properties: [
                    new OA\Property(property: "purchase_order_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "date", type: "string", format: "date"),
                    new OA\Property(property: "notes", type: "string"),
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "purchase_order_item_id", type: "integer"),
                            new OA\Property(property: "qty_received", type: "number"),
                            new OA\Property(property: "qty_rejected", type: "number"),
                            new OA\Property(property: "notes", type: "string"),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Goods receipt created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchase_order_uuid'                   => 'required|string|exists:purchase_orders,uuid',
            'date'                                  => 'required|date',
            'notes'                                 => 'nullable|string',
            'items'                                 => 'required|array|min:1',
            'items.*.purchase_order_item_id'        => 'required|integer|exists:purchase_order_items,id',
            'items.*.qty_received'                  => 'required|numeric|min:0',
            'items.*.qty_rejected'                  => 'nullable|numeric|min:0',
            'items.*.notes'                         => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $po = PurchaseOrder::where('uuid', $validated['purchase_order_uuid'])->firstOrFail();

            $receipt = GoodsReceipt::create([
                'number'            => 'GR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'purchase_order_id' => $po->id,
                'date'              => $validated['date'],
                'received_by_id'    => $request->user()->id,
                'notes'             => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $receipt->items()->create([
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'qty_received'           => $item['qty_received'],
                    'qty_rejected'           => $item['qty_rejected'] ?? 0,
                    'notes'                  => $item['notes'] ?? null,
                ]);
            }

            $po->update(['status' => 'partial']);

            return response()->json(['message' => 'Goods receipt created successfully', 'data' => $receipt->load('items')], 201);
        });
    }

    #[OA\Get(
        path: "/api/platform/purchasing/goods-receipts/{uuid}",
        summary: "Get goods receipt detail",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Goods Receipts"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 200, description: "Goods receipt detail")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $receipt = GoodsReceipt::with(['order.supplier', 'items.orderItem', 'receiver'])->where('uuid', $uuid)->firstOrFail();
        return response()->json(['message' => 'Goods receipt detail', 'data' => $receipt]);
    }
}
