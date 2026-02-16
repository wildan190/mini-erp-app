<?php

namespace App\Http\Controllers\Platform\Api\HRM;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Employee\StoreEmployeeRequest;
use App\Http\Requests\Platform\HRM\Employee\UpdateEmployeeRequest;
use App\Services\HRM\EmployeeService;
use App\Services\HRM\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Employees", description: "API Endpoints for Employee Management")]
class EmployeeController extends Controller
{
    protected EmployeeService $employeeService;
    protected FaceRecognitionService $faceRecognitionService;

    public function __construct(EmployeeService $employeeService, FaceRecognitionService $faceRecognitionService)
    {
        $this->employeeService = $employeeService;
        $this->faceRecognitionService = $faceRecognitionService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/employees",
        summary: "List all employees",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $perPage = request()->input('per_page', 15);
        $employees = $this->employeeService->getAllEmployees($perPage);
        return response()->json([
            'message' => 'List of employees',
            'data' => $employees
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/employees",
        summary: "Create a new employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        responses: [
            new OA\Response(response: 201, description: "Employee created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->createEmployee($request->validated());
        return response()->json([
            'message' => 'Employee created successfully',
            'data' => $employee
        ], 201);
    }

    #[OA\Get(
        path: "/api/platform/hrm/employees/{id}",
        summary: "Get employee details",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function show($id): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json([
            'message' => 'Employee details',
            'data' => $employee
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/employees/{id}",
        summary: "Update an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Employee updated"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function update(UpdateEmployeeRequest $request, $id): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $updatedEmployee = $this->employeeService->updateEmployee($employee, $request->validated());
        return response()->json([
            'message' => 'Employee updated successfully',
            'data' => $updatedEmployee
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/employees/{id}",
        summary: "Delete an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Employee deleted"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function destroy($id): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $this->employeeService->deleteEmployee($employee);
        return response()->json([
            'message' => 'Employee deleted successfully'
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/employees/{id}/enroll-face",
        summary: "Enroll employee face for recognition",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["face_image"],
                    properties: [
                        new OA\Property(property: "face_image", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Face enrolled successfully"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function enrollFace(Request $request, $id): JsonResponse
    {
        $request->validate([
            'face_image' => 'required|image|max:5120',
        ]);

        $employee = $this->employeeService->findEmployee($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $result = $this->faceRecognitionService->enrollFace($employee, $request->file('face_image'));

        $employee->update(['requires_face_verification' => true]);

        return response()->json([
            'message' => $result['message'],
            'data' => $employee
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/employees/{id}/face-data",
        summary: "Remove employee face data",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Face data removed"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function removeFace($id): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $this->faceRecognitionService->removeFaceData($employee);

        return response()->json([
            'message' => 'Face data removed successfully'
        ]);
    }
}
