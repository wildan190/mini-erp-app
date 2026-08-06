<?php

namespace App\Domain\Finance\Controllers;

use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\Finance\Services\FinanceAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance FP&A", description: "Financial Planning and Analysis with AI Linear Regression")]
class FPAnalysisController extends FinanceBaseController
{
    protected $aiService;

    public function __construct(FinanceAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    #[OA\Get(
        path: "/api/platform/finance/fpa/revenue-analysis",
        summary: "Revenue Trend Analysis (Linear Regression)",
        security: [["sanctum" => []]],
        tags: ["Finance FP&A"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function revenueAnalysis()
    {
        $isPgSql = DB::connection()->getDriverName() === 'pgsql';
        $monthRaw = $isPgSql ? "TO_CHAR(record_date, 'YYYY-MM')" : 'strftime("%Y-%m", record_date)';

        $monthlyRevenue = FinancialRecord::where('type', 'revenue')
            ->select(
                DB::raw("$monthRaw as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('total', 'month')
            ->toArray();

        if (count($monthlyRevenue) < 2) {
            return $this->error("Not enough historical data for linear regression (minimum 2 months required).");
        }

        // Prepare data for linear regression
        $values = array_values($monthlyRevenue);
        $analysis = $this->aiService->linearRegression($values);

        // Predict next 3 months
        $n = count($values);
        $predictions = [];
        for ($i = 0; $i < 3; $i++) {
            $predictions[] = [
                'month_offset' => $i + 1,
                'predicted_value' => $this->aiService->predictLinear($n + $i, $analysis['slope'], $analysis['intercept'])
            ];
        }

        return $this->success([
            'model' => $analysis,
            'historical' => $monthlyRevenue,
            'predictions' => $predictions
        ]);
    }
}
