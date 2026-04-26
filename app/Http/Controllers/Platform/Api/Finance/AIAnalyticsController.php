<?php

namespace App\Http\Controllers\Platform\Api\Finance;

use App\Http\Controllers\Controller;
use App\Services\AI\FinanceAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance AI Analytics", description: "Predictive Enterprise Analytics")]
class AIAnalyticsController extends Controller
{
    protected $aiService;

    public function __construct(FinanceAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    #[OA\Get(
        path: "/api/platform/finance/ai/budget-variance/{account_uuid}",
        summary: "Predict Budget Variance (Regression)",
        security: [["sanctum" => []]],
        tags: ["Finance AI Analytics"],
        parameters: [
            new OA\Parameter(name: "account_uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function budgetVariance($accountUuid): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->aiService->predictBudgetVariance($accountUuid)
        ]);
    }

    #[OA\Post(
        path: "/api/platform/finance/ai/suggest-account",
        summary: "Suggest GL Account from Description (KNN)",
        security: [["sanctum" => []]],
        tags: ["Finance AI Analytics"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "description", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function suggestAccount(Request $request): JsonResponse
    {
        $validated = $request->validate(['description' => 'required|string']);
        return response()->json([
            'status' => 'success',
            'data' => $this->aiService->suggestAccount($validated['description'])
        ]);
    }
}
