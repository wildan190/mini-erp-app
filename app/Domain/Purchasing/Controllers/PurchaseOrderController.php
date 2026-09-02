<?php

namespace App\Domain\Purchasing\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Purchasing\Models\PurchaseRequest;
use App\Domain\Purchasing\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Purchasing - Purchase Orders", description: "Purchase Order management with items")]
class PurchaseOrderController extends Controller
{
    #[OA\Get(
        path: "/api/platform/purchasing/orders",
        summary: "List purchase orders",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Orders"],
        parameters: [
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["draft", "approved", "partial", "completed", "cancelled"])),
            new OA\Parameter(name: "supplier_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [new OA\Response(response: 200, description: "List of purchase orders")]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier', 'items'])->orderBy('created_at', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_uuid')) {
            $query->whereHas('supplier', fn($q) => $q->where('uuid', $request->supplier_uuid));
        }
        return response()->json(['message' => 'List of purchase orders', 'data' => $query->paginate($request->input('per_page', 15))]);
    }

    #[OA\Post(
        path: "/api/platform/purchasing/orders",
        summary: "Create purchase order",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Orders"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["supplier_uuid", "date", "items"],
                properties: [
                    new OA\Property(property: "supplier_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "purchase_request_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "date", type: "string", format: "date"),
                    new OA\Property(property: "eta", type: "string", format: "date"),
                    new OA\Property(property: "notes", type: "string"),
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "item_name", type: "string"),
                            new OA\Property(property: "qty", type: "number"),
                            new OA\Property(property: "price", type: "number"),
                            new OA\Property(property: "tax_rate", type: "number"),
                            new OA\Property(property: "discount", type: "number"),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Purchase order created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_uuid'               => 'required|string|exists:suppliers,uuid',
            'purchase_request_uuid'       => 'nullable|string|exists:purchase_requests,uuid',
            'date'                        => 'required|date',
            'eta'                         => 'nullable|date',
            'notes'                       => 'nullable|string',
            'items'                       => 'required|array|min:1',
            'items.*.item_name'           => 'required|string',
            'items.*.qty'                 => 'required|numeric|min:0.01',
            'items.*.price'               => 'required|numeric|min:0',
            'items.*.tax_rate'            => 'nullable|numeric|min:0|max:100',
            'items.*.discount'            => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $supplier = Supplier::where('uuid', $validated['supplier_uuid'])->firstOrFail();
            $pr = null;
            if (!empty($validated['purchase_request_uuid'])) {
                $pr = PurchaseRequest::where('uuid', $validated['purchase_request_uuid'])->first();
            }

            $subtotal = 0;
            $taxTotal = 0;
            foreach ($validated['items'] as $item) {
                $lineTotal  = ($item['qty'] * $item['price']) - ($item['discount'] ?? 0);
                $subtotal  += $lineTotal;
                $taxTotal  += $lineTotal * (($item['tax_rate'] ?? 0) / 100);
            }

            $po = PurchaseOrder::create([
                'number'              => 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'supplier_id'         => $supplier->id,
                'purchase_request_id' => $pr?->id,
                'date'                => $validated['date'],
                'eta'                 => $validated['eta'] ?? null,
                'notes'               => $validated['notes'] ?? null,
                'subtotal'            => $subtotal,
                'tax_amount'          => $taxTotal,
                'total_amount'        => $subtotal + $taxTotal,
                'status'              => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['qty'] * $item['price']) - ($item['discount'] ?? 0);
                $po->items()->create([
                    'item_name' => $item['item_name'],
                    'qty'       => $item['qty'],
                    'price'     => $item['price'],
                    'tax_rate'  => $item['tax_rate'] ?? 0,
                    'discount'  => $item['discount'] ?? 0,
                    'total'     => $lineTotal + ($lineTotal * (($item['tax_rate'] ?? 0) / 100)),
                ]);
            }

            return response()->json(['message' => 'Purchase order created successfully', 'data' => $po->load(['supplier', 'items'])], 201);
        });
    }

    #[OA\Get(
        path: "/api/platform/purchasing/orders/{uuid}",
        summary: "Get purchase order detail",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Orders"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 200, description: "Purchase order detail")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $order = PurchaseOrder::with(['supplier', 'items', 'purchaseRequest', 'goodsReceipts'])->where('uuid', $uuid)->firstOrFail();
        return response()->json(['message' => 'Purchase order detail', 'data' => $order]);
    }

    #[OA\Patch(
        path: "/api/platform/purchasing/orders/{uuid}/status",
        summary: "Update purchase order status",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Purchase Orders"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [new OA\Property(property: "status", type: "string", enum: ["draft", "approved", "partial", "completed", "cancelled"])]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Status updated")]
    )]
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $order = PurchaseOrder::with(['supplier', 'items'])->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['status' => 'required|in:draft,approved,partial,completed,cancelled']);
        
        DB::transaction(function () use ($order, $validated, $request) {
            $order->update(['status' => $validated['status']]);

            // Enterprise ERP Standard: When PO is approved, auto-create/sync an AP Bill in Finance
            if ($validated['status'] === 'approved' && $order->supplier_id) {
                $existingBill = \App\Domain\Finance\Models\ApBill::where('reference', $order->number)->first();
                if (!$existingBill) {
                    $billDate = $order->date ?? today()->toDateString();
                    $dueDate = $order->eta ?? now()->addDays(30)->toDateString();
                    
                    $bill = \App\Domain\Finance\Models\ApBill::create([
                        'vendor_id'    => $order->supplier_id,
                        'bill_number'  => 'BILL-' . strtoupper(Str::random(8)),
                        'reference'    => $order->number,
                        'bill_date'    => $billDate,
                        'due_date'     => $dueDate,
                        'subtotal'     => $order->subtotal ?? $order->total_amount,
                        'tax_amount'   => $order->tax_amount ?? 0,
                        'total_amount' => $order->total_amount,
                        'paid_amount'  => 0,
                        'status'       => 'approved',
                        'notes'        => "Auto-generated from PO {$order->number} - " . ($order->notes ?? ''),
                        'approved_by'  => $request->user()?->id,
                        'approved_at'  => now(),
                    ]);

                    foreach ($order->items as $item) {
                        $bill->items()->create([
                            'description' => $item->item_name,
                            'quantity'    => $item->qty ?? 1,
                            'unit_price'  => $item->price ?? 0,
                            'amount'      => ($item->qty ?? 1) * ($item->price ?? 0),
                        ]);
                    }
                }
            }
        });

        return response()->json(['message' => 'Purchase order status updated', 'data' => $order->fresh()]);
    }
}
