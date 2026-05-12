<?php

namespace App\Http\Controllers\Platform\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseInvoice;
use App\Models\Purchasing\PurchaseOrder;
use App\Services\Purchasing\PurchasingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseInvoiceController extends Controller
{
    protected $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    public function index()
    {
        $invoices = PurchaseInvoice::with(['supplier', 'order', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $invoices]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_uuid' => 'required|string|exists:suppliers,uuid',
            'purchase_order_uuid' => 'nullable|string|exists:purchase_orders,uuid',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $supplier = \App\Models\Purchasing\Supplier::where('uuid', $validated['supplier_uuid'])->firstOrFail();
        $po = null;
        if (!empty($validated['purchase_order_uuid'])) {
            $po = PurchaseOrder::where('uuid', $validated['purchase_order_uuid'])->first();
        }

        $subtotal = 0;
        $items = [];
        foreach ($validated['items'] as $item) {
            $total = $item['qty'] * $item['price'];
            $subtotal += $total;
            $items[] = ['item_name' => $item['item_name'], 'qty' => $item['qty'], 'price' => $item['price'], 'total' => $total];
        }

        $invoiceData = [
            'number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po?->id,
            'date' => $validated['date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'total_amount' => $subtotal,
            'status' => 'draft',
        ];

        $invoice = $this->purchasingService->createInvoice($invoiceData, $items);

        return response()->json(['success' => true, 'data' => $invoice->load(['supplier', 'items'])], 201);
    }

    public function show($uuid)
    {
        $invoice = PurchaseInvoice::with(['supplier', 'order', 'items'])
            ->where('uuid', $uuid)->firstOrFail();
        return response()->json(['success' => true, 'data' => $invoice]);
    }
}
