<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Payroll\GeneratePayrollRequest;
use App\Http\Requests\Platform\HRM\Payroll\StorePayrollPeriodRequest;
use App\Models\HRM\PayrollPeriod;
use App\Services\HRM\PayrollService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Payroll Periods", description: "API Endpoints for Payroll Periods")]
class PayrollPeriodController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/payroll-periods",
        summary: "List all payroll periods",
        security: [["sanctum" => []]],
        tags: ["HRM Payroll Periods"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $periods = PayrollPeriod::latest()->get();
        return response()->json([
            'message' => 'List of payroll periods',
            'data' => $periods
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/payroll-periods",
        summary: "Create a new payroll period",
        security: [["sanctum" => []]],
        tags: ["HRM Payroll Periods"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["name", "start_date", "end_date"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "start_date", type: "string", format: "date"),
                        new OA\Property(property: "end_date", type: "string", format: "date")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Period created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StorePayrollPeriodRequest $request): JsonResponse
    {
        $period = PayrollPeriod::create($request->validated());
        return response()->json([
            'message' => 'Payroll period created successfully',
            'data' => $period
        ], 201);
    }

    #[OA\Post(
        path: "/api/platform/hrm/payroll-periods/generate",
        summary: "Generate payroll for a period",
        security: [["sanctum" => []]],
        tags: ["HRM Payroll Periods"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["payroll_period_id"],
                    properties: [
                        new OA\Property(property: "payroll_period_id", type: "integer")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Payroll generated"),
            new OA\Response(response: 400, description: "Error")
        ]
    )]
    public function generate(GeneratePayrollRequest $request): JsonResponse
    {
        try {
            $period = PayrollPeriod::findOrFail($request->payroll_period_id);
            $count = $this->payrollService->generatePayroll($period);
            return response()->json([
                'message' => "Payroll generated for $count employees.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage() . ' ' . $e->getTraceAsString()], 400);
        }
    }
}
