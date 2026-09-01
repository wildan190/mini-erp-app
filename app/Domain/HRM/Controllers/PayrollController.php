<?php

namespace App\Domain\HRM\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\HRM\Services\PayrollService;
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
        $user = request()->user();
        $filters = request()->only(['payroll_period_uuid', 'employee_uuid', 'status', 'view']);
        $perPage = request()->input('per_page', 15);

        // Check if user has HR or admin access
        $isHrOrAdmin = $user && (
            $user->hasRole('super-admin') ||
            $user->hasRole('hr-manager') ||
            $user->hasRole('hr-admin') ||
            $user->hasPermission('hrm.payroll.manage') ||
            $user->hasPermission('hrm.employees.manage')
        );

        // Non-HR users OR HR users requesting their own view should be scoped to their employee record
        if (!$isHrOrAdmin || ($filters['view'] ?? '') === 'mine') {
            $employee = $user ? \App\Domain\HRM\Models\Employee::where('user_id', $user->id)->first() : null;
            $filters['employee_id'] = $employee?->id ?? 0;
        }
        unset($filters['view']);

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

    #[OA\Get(
        path: "/api/platform/hrm/payrolls/{uuid}/payslip",
        summary: "Generate payslip PDF",
        security: [["sanctum" => []]],
        tags: ["HRM Payrolls"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Payslip PDF"),
            new OA\Response(response: 404, description: "Payroll not found")
        ]
    )]
    public function payslip($uuid)
    {
        $payroll = $this->payrollService->findPayroll($uuid);
        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }
        return $this->payrollService->generatePayslipPdf($payroll);
    }

    #[OA\Post(
        path: "/api/platform/hrm/payrolls/{uuid}/approve",
        summary: "Approve a payroll record",
        security: [["sanctum" => []]],
        tags: ["HRM Payrolls"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Payroll approved"),
            new OA\Response(response: 404, description: "Payroll not found")
        ]
    )]
    public function approve($uuid): JsonResponse
    {
        $payroll = $this->payrollService->findPayroll($uuid);
        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }
        $payroll->update(['status' => 'approved']);
        return response()->json([
            'message' => 'Payroll approved successfully',
            'data' => $payroll
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/payrolls/batch-approve",
        summary: "Batch approve payrolls",
        security: [["sanctum" => []]],
        tags: ["HRM Payrolls"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "payroll_uuids", type: "array", items: new OA\Items(type: "string", format: "uuid"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Payrolls approved"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function batchApprove(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'payroll_uuids' => 'required|array',
            'payroll_uuids.*' => 'required|exists:payrolls,uuid',
        ]);

        $count = $this->payrollService->batchApprovePayrolls($request->payroll_uuids);

        return response()->json([
            'message' => "Successfully approved $count payrolls.",
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/payrolls/batch-pay",
        summary: "Batch mark payrolls as paid",
        security: [["sanctum" => []]],
        tags: ["HRM Payrolls"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "payroll_uuids", type: "array", items: new OA\Items(type: "string", format: "uuid"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Payrolls marked as paid"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function batchPay(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'payroll_uuids' => 'required|array',
            'payroll_uuids.*' => 'required|exists:payrolls,uuid',
        ]);

        $count = $this->payrollService->batchPayPayrolls($request->payroll_uuids);

        return response()->json([
            'message' => "Successfully marked $count payrolls as paid.",
        ]);
    }
}


