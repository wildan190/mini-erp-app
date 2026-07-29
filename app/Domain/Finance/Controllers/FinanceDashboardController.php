<?php

namespace App\Domain\Finance\Controllers;

use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\Finance\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance Analytics", description: "AI-powered Finance and Analytics Endpoints")]
class FinanceDashboardController extends FinanceBaseController
{
    #[OA\Get(
        path: "/api/platform/finance/dashboard",
        summary: "Get Finance Dashboard Summary",
        security: [["sanctum" => []]],
        tags: ["Finance Analytics"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function index()
    {
        $totalRevenue = FinancialRecord::where('type', 'revenue')->sum('amount');
        $totalExpense = FinancialRecord::where('type', 'expense')->sum('amount');
        $netProfit = $totalRevenue - $totalExpense;

        $recentTransactions = FinancialRecord::orderBy('record_date', 'desc')->limit(5)->get();

        $isPgSql = DB::connection()->getDriverName() === 'pgsql';
        $monthRaw = $isPgSql ? "TO_CHAR(record_date, 'YYYY-MM')" : 'strftime("%Y-%m", record_date)';

        $monthlyData = FinancialRecord::select(
            DB::raw("$monthRaw as month"),
            DB::raw("SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) as revenue"),
            DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
        )
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        return $this->success([
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'net_profit' => $netProfit,
                'profit_margin' => $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0,
            ],
            'monthly_trends' => $monthlyData,
            'recent_transactions' => $recentTransactions
        ]);
    }
}
