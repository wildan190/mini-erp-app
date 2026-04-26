<?php

namespace App\Http\Controllers\Platform\Api\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\AccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance Reporting", description: "Enterprise Financial Reports (IFRS)")]
class ReportingController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    #[OA\Get(
        path: "/api/platform/finance/reporting/profit-loss",
        summary: "Get Profit & Loss Report",
        security: [["sanctum" => []]],
        tags: ["Finance Reporting"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function profitAndLoss(Request $request): JsonResponse
    {
        $start = $request->query('start_date', now()->startOfYear()->toDateString());
        $end = $request->query('end_date', now()->toDateString());

        return response()->json([
            'status' => 'success',
            'data' => $this->accountingService->getProfitAndLoss($start, $end)
        ]);
    }

    #[OA\Get(
        path: "/api/platform/finance/reporting/balance-sheet",
        summary: "Get Balance Sheet Report",
        security: [["sanctum" => []]],
        tags: ["Finance Reporting"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function balanceSheet(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());

        return response()->json([
            'status' => 'success',
            'data' => $this->accountingService->getBalanceSheet($date)
        ]);
    }

    #[OA\Get(
        path: "/api/platform/finance/reporting/cash-flow",
        summary: "Get Cash Flow Statement",
        security: [["sanctum" => []]],
        tags: ["Finance Reporting"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function cashFlow(Request $request): JsonResponse
    {
        $start = $request->query('start_date', now()->startOfYear()->toDateString());
        $end = $request->query('end_date', now()->toDateString());

        return response()->json([
            'status' => 'success',
            'data' => $this->accountingService->getCashFlowStatement($start, $end)
        ]);
    }
}
