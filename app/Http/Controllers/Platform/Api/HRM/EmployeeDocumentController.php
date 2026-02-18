<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\EmployeeDocument\StoreEmployeeDocumentRequest;
use App\Models\HRM\Employee;
use App\Models\HRM\EmployeeDocument;
use App\Services\HRM\EmployeeDocumentService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Employee Documents", description: "API Endpoints for Employee Document Management")]
class EmployeeDocumentController extends Controller
{
    protected EmployeeDocumentService $documentService;
    protected \App\Services\HRM\EmployeeService $employeeService;

    public function __construct(
        EmployeeDocumentService $documentService,
        \App\Services\HRM\EmployeeService $employeeService
    ) {
        $this->documentService = $documentService;
        $this->employeeService = $employeeService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/employees/{employeeUuid}/documents",
        summary: "List all documents for an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employee Documents"],
        parameters: [
            new OA\Parameter(name: "employeeUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index($employeeUuid): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($employeeUuid);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json([
            'message' => 'List of documents',
            'data' => $employee->documents
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/employees/{employeeUuid}/documents",
        summary: "Upload a new document for an employee",
        security: [["sanctum" => []]],
        tags: ["HRM Employee Documents"],
        parameters: [
            new OA\Parameter(name: "employeeUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["type", "file"],
                    properties: [
                        new OA\Property(property: "type", type: "string", enum: ["ktp", "npwp", "contract", "certificate", "other"]),
                        new OA\Property(property: "file", type: "string", format: "binary"),
                        new OA\Property(property: "expiry_date", type: "string", format: "date"),
                        new OA\Property(property: "description", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Document uploaded"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreEmployeeDocumentRequest $request, $employeeUuid): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($employeeUuid);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        $data = $request->validated();
        $data['employee_id'] = $employee->id;

        $document = $this->documentService->uploadDocument($data, $request->file('file'));

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/documents/{uuid}",
        summary: "Delete a document",
        security: [["sanctum" => []]],
        tags: ["HRM Employee Documents"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Document deleted"),
            new OA\Response(response: 404, description: "Document not found")
        ]
    )]
    public function destroy($uuid): JsonResponse
    {
        $document = $this->documentService->findDocument($uuid);
        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        $this->documentService->deleteDocument($document);

        return response()->json([
            'message' => 'Document deleted successfully'
        ]);
    }
}
