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
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "department_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "designation_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["active", "inactive", "terminated", "resigned"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $filters = request()->only(['department_uuid', 'designation_uuid', 'status']);
        $perPage = request()->input('per_page', 15);
        $employees = $this->employeeService->getAllEmployees($perPage, $filters);
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["first_name", "last_name"],
                    properties: [
                        new OA\Property(property: "user_uuid", type: "string", format: "uuid", description: "Existing User UUID (optional)"),
                        new OA\Property(property: "first_name", type: "string", description: "First Name"),
                        new OA\Property(property: "last_name", type: "string", description: "Last Name"),
                        new OA\Property(property: "email", type: "string", format: "email", description: "Email (required if user_uuid is null)"),
                        new OA\Property(property: "password", type: "string", format: "password", description: "Password (required if user_uuid is null)"),
                        new OA\Property(property: "department_uuid", type: "string", format: "uuid", description: "Department UUID"),
                        new OA\Property(property: "designation_uuid", type: "string", format: "uuid", description: "Designation UUID"),
                        new OA\Property(property: "emp_code", type: "string", description: "Employee code"),
                        new OA\Property(property: "joining_date", type: "string", format: "date", description: "Joining date"),
                        new OA\Property(property: "status", type: "string", enum: ["active", "inactive", "terminated", "resigned"]),
                        new OA\Property(property: "nik", type: "string", description: "National ID number"),
                        new OA\Property(property: "place_of_birth", type: "string", description: "Place of birth"),
                        new OA\Property(property: "date_of_birth", type: "string", format: "date", description: "Date of birth"),
                        new OA\Property(property: "gender", type: "string", enum: ["male", "female"]),
                        new OA\Property(property: "marital_status", type: "string", enum: ["single", "married", "divorced", "widowed"]),
                        new OA\Property(property: "religion", type: "string", description: "Religion"),
                        new OA\Property(property: "address", type: "string", description: "Address"),
                        new OA\Property(property: "phone", type: "string", description: "Phone number"),
                        new OA\Property(property: "emergency_contact_name", type: "string", description: "Emergency contact name"),
                        new OA\Property(property: "emergency_contact_phone", type: "string", description: "Emergency contact phone")
                    ]
                )
            )
        ),
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
        path: "/api/platform/hrm/employees/{uuid}",
        summary: "Get employee details",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function show($uuid): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($uuid);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json([
            'message' => 'Employee details',
            'data' => $employee
        ]);
    }

    #[OA\Put(
        path: "/api/platform/hrm/employees/{uuid}",
        summary: "Update an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "user_uuid", type: "string", format: "uuid", description: "Existing User UUID"),
                        new OA\Property(property: "first_name", type: "string", description: "First Name"),
                        new OA\Property(property: "last_name", type: "string", description: "Last Name"),
                        new OA\Property(property: "department_uuid", type: "string", format: "uuid", description: "Department UUID"),
                        new OA\Property(property: "designation_uuid", type: "string", format: "uuid", description: "Designation UUID"),
                        new OA\Property(property: "emp_code", type: "string", description: "Employee code"),
                        new OA\Property(property: "joining_date", type: "string", format: "date", description: "Joining date"),
                        new OA\Property(property: "status", type: "string", enum: ["active", "inactive", "terminated", "resigned"]),
                        new OA\Property(property: "nik", type: "string", description: "National ID number"),
                        new OA\Property(property: "place_of_birth", type: "string", description: "Place of birth"),
                        new OA\Property(property: "date_of_birth", type: "string", format: "date", description: "Date of birth"),
                        new OA\Property(property: "gender", type: "string", enum: ["male", "female"]),
                        new OA\Property(property: "marital_status", type: "string", enum: ["single", "married", "divorced", "widowed"]),
                        new OA\Property(property: "religion", type: "string", description: "Religion"),
                        new OA\Property(property: "address", type: "string", description: "Address"),
                        new OA\Property(property: "phone", type: "string", description: "Phone number"),
                        new OA\Property(property: "emergency_contact_name", type: "string", description: "Emergency contact name"),
                        new OA\Property(property: "emergency_contact_phone", type: "string", description: "Emergency contact phone")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Employee updated"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function update(UpdateEmployeeRequest $request, $uuid): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($uuid);

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
        path: "/api/platform/hrm/employees/{uuid}",
        summary: "Delete an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Employee deleted"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function destroy($uuid): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($uuid);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $this->employeeService->deleteEmployee($employee);
        return response()->json([
            'message' => 'Employee deleted successfully'
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/employees/{uuid}/enroll-face",
        summary: "Enroll employee face for recognition",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
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
    public function enrollFace(Request $request, $uuid): JsonResponse
    {
        $request->validate([
            'face_image' => 'required|image|max:5120',
        ]);

        $employee = $this->employeeService->findEmployee($uuid);

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
        path: "/api/platform/hrm/employees/{uuid}/face-data",
        summary: "Remove employee face data",
        security: [["sanctum" => []]],
        tags: ["HRM Employees"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Face data removed"),
            new OA\Response(response: 404, description: "Employee not found")
        ]
    )]
    public function removeFace($uuid): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($uuid);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $this->faceRecognitionService->removeFaceData($employee);

        return response()->json([
            'message' => 'Face data removed successfully'
        ]);
    }
}
