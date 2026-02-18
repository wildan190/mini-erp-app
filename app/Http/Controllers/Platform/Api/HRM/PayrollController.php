<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Models\HRM\Payroll;
use App\Services\HRM\PayrollService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Payrolls", description: "API Endpoints for Payrolls")]
class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/payrolls",
        summary: "List payrolls",
        security: [["sanctum" => []]],
        tags: ["HRM Payrolls"],
        parameters: [
            new OA\Parameter(name: "payroll_period_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "employee_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["draft", "paid"])),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $filters = request()->only(['payroll_period_uuid', 'employee_uuid', 'status']);
        $perPage = request()->input('per_page', 15);
        $payrolls = $this->payrollService->getPayrolls($filters, $perPage);
        return response()->json([
            'message' => 'List of payrolls',
            'data' => $payrolls
        ]);
    }

    #[OA\Get(
        path: "/api/platform/hrm/payrolls/{uuid}",
        summary: "Get payroll details",
        security: [["sanctum" => []]],
        tags: ["HRM Payrolls"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Payroll not found")
        ]
    )]
    public function show($uuid): JsonResponse
    {
        $payroll = $this->payrollService->findPayroll($uuid);
        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }
        return response()->json([
            'message' => 'Payroll details',
            'data' => $payroll
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/payrolls/{uuid}/pay",
        summary: "Mark payroll as paid",
        security: [["sanctum" => []]],
        tags: ["HRM Payrolls"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Payroll paid"),
            new OA\Response(response: 404, description: "Payroll not found")
        ]
    )]
    public function pay($uuid): JsonResponse
    {
        $payroll = $this->payrollService->findPayroll($uuid);
        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }
        $paidPayroll = $this->payrollService->payPayroll($payroll);
        return response()->json([
            'message' => 'Payroll marked as paid',
            'data' => $paidPayroll
        ]);
    }
}
