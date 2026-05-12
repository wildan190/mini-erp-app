<?php

namespace App\Http\Controllers\Platform\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_uuid' => 'required|string|exists:suppliers,uuid',
            'purchase_request_uuid' => 'nullable|string|exists:purchase_requests,uuid',
            'date' => 'required|date',
            'eta' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $supplier = \App\Models\Purchasing\Supplier::where('uuid', $validated['supplier_uuid'])->firstOrFail();
            $pr = null;
            if (!empty($validated['purchase_request_uuid'])) {
                $pr = \App\Models\Purchasing\PurchaseRequest::where('uuid', $validated['purchase_request_uuid'])->first();
            }

            $subtotal = 0;
            $taxTotal = 0;
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['qty'] * $item['price'] - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
                $taxTotal += $lineTotal * (($item['tax_rate'] ?? 0) / 100);
            }

            $po = PurchaseOrder::create([
                'number' => 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'supplier_id' => $supplier->id,
                'purchase_request_id' => $pr?->id,
                'date' => $validated['date'],
                'eta' => $validated['eta'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $subtotal + $taxTotal,
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['qty'] * $item['price'] - ($item['discount'] ?? 0);
                $po->items()->create([
                    'item_name' => $item['item_name'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'total' => $lineTotal + ($lineTotal * (($item['tax_rate'] ?? 0) / 100)),
                ]);
            }

            return response()->json(['success' => true, 'data' => $po->load(['supplier', 'items'])], 201);
        });
    }

    public function show($uuid)
    {
        $order = PurchaseOrder::with(['supplier', 'items', 'purchaseRequest', 'goodsReceipts'])
            ->where('uuid', $uuid)->firstOrFail();
        return response()->json(['success' => true, 'data' => $order]);
    }

    public function updateStatus(Request $request, $uuid)
    {
        $order = PurchaseOrder::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'status' => 'required|in:draft,approved,partial,completed,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'data' => $order]);
    }
}
