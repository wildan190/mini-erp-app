<?php

namespace App\Http\Controllers\Platform\Api\Finance;

use App\Models\Finance\FinancialRecord;
use App\Models\Finance\CashForecast;
use App\Services\AI\FinanceAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance Forecasting", description: "Predictive Analytics for Cash Position")]
class ForecastingController extends FinanceBaseController
{
    protected $aiService;

    public function __construct(FinanceAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    #[OA\Get(
        path: "/api/platform/finance/forecasting/cash-forecast",
        summary: "Cash Position Forecast",
        security: [["sanctum" => []]],
        tags: ["Finance Forecasting"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function cashForecast()
    {
        $isPgSql = DB::connection()->getDriverName() === 'pgsql';
        $monthRaw = $isPgSql ? "TO_CHAR(record_date, 'YYYY-MM')" : 'strftime("%Y-%m", record_date)';
        $netRaw = "SUM(CASE WHEN type = 'revenue' THEN amount ELSE -amount END)";

        $monthlyNet = FinancialRecord::select(
                DB::raw("$monthRaw as month"),
                DB::raw("$netRaw as net")
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('net', 'month')
            ->toArray();

        if (count($monthlyNet) < 2) {
            return $this->error("Insufficient historical data for forecasting.");
        }

        $forecastResults = $this->aiService->forecast(array_values($monthlyNet), 6);

        return $this->success([
            'historical_net' => $monthlyNet,
            'forecast_6_months' => $forecastResults
        ]);
    }
}
