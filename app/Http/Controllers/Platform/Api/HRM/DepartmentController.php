<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Department\StoreDepartmentRequest;
use App\Http\Requests\Platform\HRM\Department\UpdateDepartmentRequest;
use App\Services\HRM\DepartmentService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Departments", description: "API Endpoints for Department Management")]
class DepartmentController extends Controller
{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/departments",
        summary: "List all departments",
        security: [["sanctum" => []]],
        tags: ["HRM Departments"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'List of departments',
            'data' => $this->departmentService->getAllDepartments()
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/departments",
        summary: "Create a new department",
        security: [["sanctum" => []]],
        tags: ["HRM Departments"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["name"],
                    properties: [
                        new OA\Property(property: "name", type: "string", description: "Department name"),
                        new OA\Property(property: "description", type: "string", description: "Optional description")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Department created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());
        return response()->json([
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    #[OA\Get(
        path: "/api/platform/hrm/departments/{uuid}",
        summary: "Get department details",
        security: [["sanctum" => []]],
        tags: ["HRM Departments"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Department not found")
        ]
    )]
    public function show($uuid): JsonResponse
    {
        $department = $this->departmentService->findDepartment($uuid);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }
        return response()->json([
            'message' => 'Department details',
            'data' => $department
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/departments/{uuid}",
        summary: "Update a department",
        security: [["sanctum" => []]],
        tags: ["HRM Departments"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["name"],
                    properties: [
                        new OA\Property(property: "name", type: "string", description: "Department name"),
                        new OA\Property(property: "description", type: "string", description: "Optional description")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Department updated"),
            new OA\Response(response: 404, description: "Department not found")
        ]
    )]
    public function update(UpdateDepartmentRequest $request, $uuid): JsonResponse
    {
        $department = $this->departmentService->findDepartment($uuid);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }
        $updatedDepartment = $this->departmentService->updateDepartment($department, $request->validated());
        return response()->json([
            'message' => 'Department updated successfully',
            'data' => $updatedDepartment
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/departments/{uuid}",
        summary: "Delete a department",
        security: [["sanctum" => []]],
        tags: ["HRM Departments"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Department deleted"),
            new OA\Response(response: 404, description: "Department not found")
        ]
    )]
    public function destroy($uuid): JsonResponse
    {
        $department = $this->departmentService->findDepartment($uuid);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }
        $this->departmentService->deleteDepartment($department);
        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }
}
