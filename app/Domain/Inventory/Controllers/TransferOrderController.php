<?php

namespace App\Domain\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Inventory\Models\InventoryTransferOrder;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Inventory Transfer Orders", description: "Inter-warehouse stock transfer management")]
class TransferOrderController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = InventoryTransferOrder::with(['sourceWarehouse', 'destinationWarehouse', 'items.product'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'message' => 'List of transfer orders',
            'data'    => $transfers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_warehouse_uuid'      => 'required|string|exists:inventory_warehouses,uuid',
            'destination_warehouse_uuid' => 'required|string|exists:inventory_warehouses,uuid|different:source_warehouse_uuid',
            'transfer_date'              => 'nullable|date',
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.product_uuid'       => 'required|string|exists:inventory_products,uuid',
            'items.*.quantity'           => 'required|integer|min:1',
        ]);

        $transfer = InventoryTransferOrder::create([
            'source_warehouse_uuid'      => $validated['source_warehouse_uuid'],
            'destination_warehouse_uuid' => $validated['destination_warehouse_uuid'],
            'transfer_date'              => $validated['transfer_date'] ?? now()->toDateString(),
            'notes'                      => $validated['notes'] ?? null,
            'status'                     => 'draft',
        ]);

        foreach ($validated['items'] as $item) {
            $transfer->items()->create([
                'product_uuid'       => $item['product_uuid'],
                'quantity_requested' => $item['quantity'],
            ]);
        }

        return response()->json([
            'message' => 'Transfer order created successfully',
            'data'    => $transfer->load(['sourceWarehouse', 'destinationWarehouse', 'items.product']),
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $transfer = InventoryTransferOrder::with(['sourceWarehouse', 'destinationWarehouse', 'items.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'message' => 'Transfer order detail',
            'data'    => $transfer,
        ]);
    }

    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:in_transit,completed,cancelled',
        ]);

        $transfer = $this->inventoryService->updateTransferOrderStatus($uuid, $validated['status']);

        return response()->json([
            'message' => 'Transfer order status updated to ' . $validated['status'],
            'data'    => $transfer->load(['sourceWarehouse', 'destinationWarehouse', 'items.product']),
        ]);
    }
}
