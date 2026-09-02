<?php

namespace App\Domain\Purchasing\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Purchasing\Models\PurchaseInvoice;
use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Purchasing\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Purchasing - Invoices", description: "Purchase invoice management and payment tracking")]
class PurchaseInvoiceController extends Controller
{
    #[OA\Get(
        path: "/api/platform/purchasing/invoices",
        summary: "List purchase invoices",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Invoices"],
        parameters: [
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["draft", "open", "paid", "cancelled"])),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [new OA\Response(response: 200, description: "List of purchase invoices")]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseInvoice::with(['supplier', 'order', 'items'])->orderBy('created_at', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        return response()->json(['message' => 'List of purchase invoices', 'data' => $query->paginate($request->input('per_page', 15))]);
    }

    #[OA\Post(
        path: "/api/platform/purchasing/invoices",
        summary: "Create purchase invoice",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Invoices"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["supplier_uuid", "date", "due_date", "items"],
                properties: [
                    new OA\Property(property: "supplier_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "purchase_order_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "date", type: "string", format: "date"),
                    new OA\Property(property: "due_date", type: "string", format: "date"),
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "item_name", type: "string"),
                            new OA\Property(property: "qty", type: "number"),
                            new OA\Property(property: "price", type: "number"),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Invoice created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_uuid'       => 'required|string|exists:suppliers,uuid',
            'purchase_order_uuid' => 'nullable|string|exists:purchase_orders,uuid',
            'date'                => 'required|date',
            'due_date'            => 'required|date|after_or_equal:date',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.item_name'   => 'required|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.price'       => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $supplier = Supplier::where('uuid', $validated['supplier_uuid'])->firstOrFail();
            $po = null;
            if (!empty($validated['purchase_order_uuid'])) {
                $po = PurchaseOrder::where('uuid', $validated['purchase_order_uuid'])->first();
            }

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['qty'] * $item['price'];
            }

            $invoice = PurchaseInvoice::create([
                'number'           => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'supplier_id'      => $supplier->id,
                'purchase_order_id'=> $po?->id,
                'date'             => $validated['date'],
                'due_date'         => $validated['due_date'],
                'notes'            => $validated['notes'] ?? null,
                'subtotal'         => $subtotal,
                'tax_amount'       => 0,
                'total_amount'     => $subtotal,
                'status'           => 'open',
            ]);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'item_name' => $item['item_name'],
                    'qty'       => $item['qty'],
                    'price'     => $item['price'],
                    'total'     => $item['qty'] * $item['price'],
                ]);
            }

            // Enterprise ERP Integration: Create AP Bill in Finance
            $existingBill = \App\Domain\Finance\Models\ApBill::where('reference', $invoice->number)->first();
            if (!$existingBill) {
                $bill = \App\Domain\Finance\Models\ApBill::create([
                    'vendor_id'    => $supplier->id,
                    'bill_number'  => 'BILL-' . strtoupper(Str::random(8)),
                    'reference'    => $invoice->number,
                    'bill_date'    => $validated['date'],
                    'due_date'     => $validated['due_date'],
                    'subtotal'     => $subtotal,
                    'tax_amount'   => 0,
                    'total_amount' => $subtotal,
                    'paid_amount'  => 0,
                    'status'       => 'approved',
                    'notes'        => "From Purchasing Invoice {$invoice->number} " . ($po ? "(PO: {$po->number})" : ''),
                    'approved_by'  => auth()->id(),
                    'approved_at'  => now(),
                ]);

                foreach ($validated['items'] as $item) {
                    $bill->items()->create([
                        'description' => $item['item_name'],
                        'quantity'    => $item['qty'],
                        'unit_price'  => $item['price'],
                        'amount'      => $item['qty'] * $item['price'],
                    ]);
                }
            }

            return response()->json(['message' => 'Invoice created successfully', 'data' => $invoice->load(['supplier', 'items'])], 201);
        });
    }

    #[OA\Get(
        path: "/api/platform/purchasing/invoices/{uuid}",
        summary: "Get invoice detail",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Invoices"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 200, description: "Invoice detail")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $invoice = PurchaseInvoice::with(['supplier', 'order', 'items'])->where('uuid', $uuid)->firstOrFail();
        return response()->json(['message' => 'Invoice detail', 'data' => $invoice]);
    }

    #[OA\Patch(
        path: "/api/platform/purchasing/invoices/{uuid}/status",
        summary: "Update invoice payment status",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Invoices"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [new OA\Property(property: "status", type: "string", enum: ["draft", "open", "paid", "cancelled"])]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Invoice status updated")]
    )]
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $invoice = PurchaseInvoice::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['status' => 'required|in:draft,open,paid,cancelled']);
        $invoice->update(['status' => $validated['status']]);
        return response()->json(['message' => 'Invoice status updated', 'data' => $invoice]);
    }
}
