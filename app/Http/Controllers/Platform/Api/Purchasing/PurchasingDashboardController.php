<?php

namespace App\Http\Controllers\Platform\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\Supplier;
use App\Models\Purchasing\PurchaseInvoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PurchasingDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $stats = [
            'total_suppliers' => Supplier::count(),
            'pending_pos' => PurchaseOrder::where('status', 'draft')->count(),
            'monthly_spend' => PurchaseInvoice::whereBetween('date', [$startOfMonth, $now])
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
            'overdue_invoices' => PurchaseInvoice::where('status', 'open')
                ->where('due_date', '<', $now->toDateString())
                ->count(),
        ];

        $recent_pos = PurchaseOrder::with('supplier')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $spend_by_category = Supplier::select('category')
            ->selectRaw('SUM(purchase_invoices.total_amount) as total')
            ->join('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->groupBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_pos' => $recent_pos,
                'spend_by_category' => $spend_by_category
            ]
        ]);
    }
}
