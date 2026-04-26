<?php

namespace App\Http\Controllers\Platform\Api\Finance;

use App\Models\Finance\InventoryMovement;
use App\Services\AI\FinanceAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance Supply Chain", description: "Supply Chain Analytics with AI K-Nearest Neighbor")]
class SupplyChainAIController extends FinanceBaseController
{
    protected $aiService;

    public function __construct(FinanceAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    #[OA\Get(
        path: "/api/platform/finance/supply-chain/risk-assessment",
        summary: "Inventory Risk Assessment (KNN)",
        security: [["sanctum" => []]],
        tags: ["Finance Supply Chain"],
        parameters: [
            new OA\Parameter(name: "avg_daily_out", in: "query", schema: new OA\Schema(type: "number", format: "float")),
            new OA\Parameter(name: "lead_time_days", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function riskAssessment(Request $request)
    {
        // Sample training set: features [avg_daily_out, lead_time_days], label
        // Labels: 0 = Low Risk, 1 = Medium Risk, 2 = High Risk
        $trainingSet = [
            ['features' => [10, 2], 'label' => 'Low Risk'],
            ['features' => [50, 5], 'label' => 'Medium Risk'],
            ['features' => [100, 10], 'label' => 'High Risk'],
            ['features' => [5, 1], 'label' => 'Low Risk'],
            ['features' => [80, 8], 'label' => 'High Risk'],
            ['features' => [40, 4], 'label' => 'Medium Risk'],
        ];

        $currentMovement = [
            $request->input('avg_daily_out', 45), // Default to some value for demo
            $request->input('lead_time_days', 6)
        ];

        $riskPrediction = $this->aiService->knn($trainingSet, $currentMovement, 3);

        return $this->success([
            'input_metrics' => [
                'avg_daily_out' => $currentMovement[0],
                'lead_time_days' => $currentMovement[1]
            ],
            'risk_category' => $riskPrediction,
            'recommendation' => $this->getRecommendation($riskPrediction)
        ]);
    }

    private function getRecommendation($risk)
    {
        switch ($risk) {
            case 'High Risk':
                return 'Immediate reorder recommended. Increase safety stock levels.';
            case 'Medium Risk':
                return 'Review inventory levels. Plan for next shipment soon.';
            default:
                return 'Inventory levels are stable. No immediate action needed.';
        }
    }
}
