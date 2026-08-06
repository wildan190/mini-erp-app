<?php

namespace App\Domain\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Inventory\Models\InventoryProduct;
use App\Domain\Inventory\Models\InventoryStock;
use App\Domain\Inventory\Models\InventoryStockMovement;
use App\Domain\Inventory\Models\InventoryTransferOrder;
use App\Domain\Inventory\Models\Warehouse;
use Illuminate\Http\JsonResponse;

class InventoryDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalSkus = InventoryProduct::count();
        $totalWarehouses = Warehouse::where('is_active', true)->count();
        $pendingTransfers = InventoryTransferOrder::whereIn('status', ['draft', 'in_transit'])->count();

        // Total Stock Valuation
        $stocks = InventoryStock::with('product')->get();
        $totalValuation = $stocks->sum(function ($stock) {
            return $stock->quantity_on_hand * ($stock->product->unit_cost ?? 0);
        });

        // Low stock items
        $lowStockItems = InventoryProduct::with('stocks.warehouse')
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->stocks->sum('quantity_on_hand');
                return $totalStock <= $product->reorder_level;
            })
            ->values();

        // Recent stock movements
        $recentMovements = InventoryStockMovement::with(['product', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'message' => 'Inventory dashboard overview',
            'data'    => [
                'stats' => [
                    'total_skus'        => $totalSkus,
                    'total_warehouses'  => $totalWarehouses,
                    'pending_transfers' => $pendingTransfers,
                    'total_valuation'   => $totalValuation,
                    'low_stock_count'   => $lowStockItems->count(),
                ],
                'low_stock_items'  => $lowStockItems,
                'recent_movements' => $recentMovements,
            ],
        ]);
    }
}
