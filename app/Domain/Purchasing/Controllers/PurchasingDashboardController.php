<?php

namespace App\Domain\Purchasing\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Purchasing\Models\PurchaseRequest;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Purchasing - Dashboard", description: "Purchasing analytics and KPI summary")]
class PurchasingDashboardController extends Controller
{
    #[OA\Get(
        path: "/api/platform/purchasing/dashboard",
        summary: "Purchasing dashboard KPIs and summary",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Dashboard"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dashboard stats",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "stats", type: "object", properties: [
                            new OA\Property(property: "total_suppliers", type: "integer"),
                            new OA\Property(property: "pending_pos", type: "integer"),
                            new OA\Property(property: "monthly_spend", type: "number"),
                            new OA\Property(property: "overdue_invoices", type: "integer"),
                        ]),
                        new OA\Property(property: "recent_pos", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "spend_by_category", type: "array", items: new OA\Items(type: "object")),
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $now           = Carbon::now();
        $startOfMonth  = $now->copy()->startOfMonth();

        $stats = [
            'total_suppliers'   => Supplier::count(),
            'pending_requests'  => PurchaseRequest::where('status', 'pending')->count(),
            'pending_pos'       => PurchaseOrder::where('status', 'draft')->count(),
            'monthly_spend'     => PurchaseInvoice::whereBetween('date', [$startOfMonth, $now])
                                        ->where('status', '!=', 'cancelled')
                                        ->sum('total_amount'),
            'overdue_invoices'  => PurchaseInvoice::where('status', 'open')
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
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'message' => 'Purchasing dashboard',
            'data'    => compact('stats', 'recent_pos', 'spend_by_category'),
        ]);
    }
}
