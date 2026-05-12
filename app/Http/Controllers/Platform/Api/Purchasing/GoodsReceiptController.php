<?php

namespace App\Http\Controllers\Platform\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\GoodsReceipt;
use App\Models\Purchasing\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $receipts = GoodsReceipt::with(['order.supplier', 'items', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $receipts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_uuid' => 'required|string|exists:purchase_orders,uuid',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|integer|exists:purchase_order_items,id',
            'items.*.qty_received' => 'required|numeric|min:0',
            'items.*.qty_rejected' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $po = PurchaseOrder::where('uuid', $validated['purchase_order_uuid'])->firstOrFail();

            $receipt = GoodsReceipt::create([
                'number' => 'GR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'purchase_order_id' => $po->id,
                'date' => $validated['date'],
                'received_by_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $receipt->items()->create([
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'qty_received' => $item['qty_received'],
                    'qty_rejected' => $item['qty_rejected'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update PO status to partial or completed
            $po->update(['status' => 'partial']);

            return response()->json(['success' => true, 'data' => $receipt->load('items')], 201);
        });
    }

    public function show($uuid)
    {
        $receipt = GoodsReceipt::with(['order.supplier', 'items.orderItem', 'receiver'])
            ->where('uuid', $uuid)->firstOrFail();
        return response()->json(['success' => true, 'data' => $receipt]);
    }
}
