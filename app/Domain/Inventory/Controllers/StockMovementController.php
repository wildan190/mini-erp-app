<?php

namespace App\Domain\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Inventory\Models\InventoryStockMovement;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Stock Movements & Ledger", description: "Stock adjustment and transaction ledger")]
class StockMovementController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = InventoryStockMovement::with(['product', 'warehouse'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('product_uuid')) {
            $query->where('product_uuid', $request->product_uuid);
        }

        if ($request->filled('warehouse_uuid')) {
            $query->where('warehouse_uuid', $request->warehouse_uuid);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $movements = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'message' => 'Stock movements log',
            'data'    => $movements,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_uuid'     => 'required|string|exists:inventory_products,uuid',
            'warehouse_uuid'   => 'required|string|exists:inventory_warehouses,uuid',
            'type'             => 'required|in:inbound,outbound,transfer_in,transfer_out,adjustment,reconciliation',
            'quantity'         => 'required|integer|not_in:0',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        $movement = $this->inventoryService->recordStockMovement(
            $validated['product_uuid'],
            $validated['warehouse_uuid'],
            $validated['type'],
            $validated['quantity'],
            $validated['reference_number'] ?? null,
            $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Stock movement recorded successfully',
            'data'    => $movement->load(['product', 'warehouse']),
        ], 201);
    }
}
