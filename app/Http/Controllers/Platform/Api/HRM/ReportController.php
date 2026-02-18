<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Services\HRM\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Reports", description: "API Endpoints for HR Analytics & Reports")]
class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/reports/turnover",
        summary: "Get employee turnover statistics",
        security: [["sanctum" => []]],
        tags: ["HRM Reports"],
        parameters: [
            new OA\Parameter(name: "start_date", in: "query", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "end_date", in: "query", schema: new OA\Schema(type: "string", format: "date"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function turnover(Request $request): JsonResponse
    {
        $stats = $this->reportService->getTurnoverStats(
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'message' => 'Turnover statistics',
            'data' => $stats
        ]);
    }

    #[OA\Get(
        path: "/api/platform/hrm/reports/labor-cost",
        summary: "Get labor cost statistics",
        security: [["sanctum" => []]],
        tags: ["HRM Reports"],
        parameters: [
            new OA\Parameter(name: "year", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function laborCost(Request $request): JsonResponse
    {
        $stats = $this->reportService->getLaborCostStats($request->input('year'));

        return response()->json([
            'message' => 'Labor cost statistics',
            'data' => $stats
        ]);
    }
}
