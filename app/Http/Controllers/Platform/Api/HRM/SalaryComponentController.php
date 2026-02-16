<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Payroll\StoreSalaryComponentRequest;
use App\Models\HRM\SalaryComponent;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Salary Components", description: "API Endpoints for Salary Components")]
class SalaryComponentController extends Controller
{
    #[OA\Get(
        path: "/api/platform/hrm/salary-components",
        summary: "List all salary components",
        security: [["sanctum" => []]],
        tags: ["HRM Salary Components"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $components = SalaryComponent::all();
        return response()->json([
            'message' => 'List of salary components',
            'data' => $components
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/salary-components",
        summary: "Create a new salary component",
        security: [["sanctum" => []]],
        tags: ["HRM Salary Components"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["name", "type", "value"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "type", type: "string", enum: ["earning", "deduction"]),
                        new OA\Property(property: "is_taxable", type: "boolean"),
                        new OA\Property(property: "is_fixed", type: "boolean"),
                        new OA\Property(property: "value", type: "number"),
                        new OA\Property(property: "percentage_of", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Component created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreSalaryComponentRequest $request): JsonResponse
    {
        $component = SalaryComponent::create($request->validated());
        return response()->json([
            'message' => 'Salary component created successfully',
            'data' => $component
        ], 201);
    }
}
