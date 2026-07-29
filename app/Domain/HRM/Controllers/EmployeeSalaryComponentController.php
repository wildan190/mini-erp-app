<?php

namespace App\Domain\HRM\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Payroll\AssignSalaryComponentRequest;
use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\SalaryComponent;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Employee Salary Components", description: "API Endpoints for assigning salary components to employees")]
class EmployeeSalaryComponentController extends Controller
{
    /**
     * Resolve employee by UUID or 404.
     */
    private function resolveEmployee(string $uuid): Employee
    {
        return Employee::where('uuid', $uuid)->firstOrFail();
    }

    #[OA\Get(
        path: "/api/platform/hrm/employees/{uuid}/salary-components",
        summary: "List salary components assigned to an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employee Salary Components"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Employee not found"),
        ]
    )]
    public function index(string $uuid): JsonResponse
    {
        $employee = $this->resolveEmployee($uuid);

        $components = $employee->salaryComponents()->get()->map(function ($component) {
            return [
                'uuid'         => $component->uuid,
                'name'         => $component->name,
                'type'         => $component->type,
                'is_fixed'     => $component->is_fixed,
                'is_taxable'   => $component->is_taxable,
                'default_value'=> $component->value,
                'custom_value' => $component->pivot->custom_value,
                'effective_value' => $component->pivot->custom_value ?? $component->value,
            ];
        });

        return response()->json([
            'message' => 'Salary components for employee',
            'data'    => $components,
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/employees/{uuid}/salary-components",
        summary: "Assign a salary component to an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employee Salary Components"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        required: ["salary_component_uuid"],
                        properties: [
                            new OA\Property(property: "salary_component_uuid", type: "string", format: "uuid", description: "UUID of the salary component"),
                            new OA\Property(property: "custom_value", type: "number", nullable: true, description: "Override value (leave empty to use component default)"),
                        ]
                    )
                ),
                new OA\MediaType(
                    mediaType: "application/x-www-form-urlencoded",
                    schema: new OA\Schema(
                        required: ["salary_component_uuid"],
                        properties: [
                            new OA\Property(property: "salary_component_uuid", type: "string", format: "uuid"),
                            new OA\Property(property: "custom_value", type: "number", nullable: true),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(response: 201, description: "Component assigned"),
            new OA\Response(response: 409, description: "Component already assigned"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 404, description: "Employee or component not found"),
        ]
    )]
    public function store(AssignSalaryComponentRequest $request, string $uuid): JsonResponse
    {
        $employee  = $this->resolveEmployee($uuid);
        $component = SalaryComponent::where('uuid', $request->salary_component_uuid)->firstOrFail();

        // Check duplicate
        if ($employee->salaryComponents()->where('salary_component_id', $component->id)->exists()) {
            return response()->json(['message' => 'This salary component is already assigned to the employee.'], 409);
        }

        $employee->salaryComponents()->attach($component->id, [
            'custom_value' => $request->custom_value,
        ]);

        return response()->json([
            'message' => 'Salary component assigned successfully',
            'data'    => [
                'employee_uuid'        => $employee->uuid,
                'salary_component_uuid'=> $component->uuid,
                'name'                 => $component->name,
                'custom_value'         => $request->custom_value,
                'effective_value'      => $request->custom_value ?? $component->value,
            ],
        ], 201);
    }

    #[OA\Put(
        path: "/api/platform/hrm/employees/{uuid}/salary-components/{componentUuid}",
        summary: "Update custom value of an assigned salary component",
        security: [["sanctum" => []]],
        tags: ["HRM Employee Salary Components"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "componentUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "custom_value", type: "number", nullable: true, description: "New override value (leave empty to use component default)"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated successfully"),
            new OA\Response(response: 404, description: "Assignment not found"),
        ]
    )]
    public function update(AssignSalaryComponentRequest $request, string $uuid, string $componentUuid): JsonResponse
    {
        $employee  = $this->resolveEmployee($uuid);
        $component = SalaryComponent::where('uuid', $componentUuid)->firstOrFail();

        if (!$employee->salaryComponents()->where('salary_component_id', $component->id)->exists()) {
            return response()->json(['message' => 'This salary component is not assigned to the employee.'], 404);
        }

        $employee->salaryComponents()->updateExistingPivot($component->id, [
            'custom_value' => $request->custom_value,
        ]);

        return response()->json([
            'message' => 'Salary component updated successfully',
            'data'    => [
                'employee_uuid'        => $employee->uuid,
                'salary_component_uuid'=> $component->uuid,
                'name'                 => $component->name,
                'custom_value'         => $request->custom_value,
                'effective_value'      => $request->custom_value ?? $component->value,
            ],
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/employees/{uuid}/salary-components/{componentUuid}",
        summary: "Remove a salary component from an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employee Salary Components"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "componentUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Removed successfully"),
            new OA\Response(response: 404, description: "Assignment not found"),
        ]
    )]
    public function destroy(string $uuid, string $componentUuid): JsonResponse
    {
        $employee  = $this->resolveEmployee($uuid);
        $component = SalaryComponent::where('uuid', $componentUuid)->firstOrFail();

        $detached = $employee->salaryComponents()->detach($component->id);

        if (!$detached) {
            return response()->json(['message' => 'This salary component is not assigned to the employee.'], 404);
        }

        return response()->json(['message' => 'Salary component removed from employee successfully.']);
    }
}
