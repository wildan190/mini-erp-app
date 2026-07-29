<?php

namespace App\Domain\HRM\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Services\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FaceRecognitionController extends Controller
{
    public function __construct(
        protected FaceRecognitionService $faceRecognitionService
    ) {}

    #[OA\Post(
        path: "/api/platform/hrm/employees/{employee}/enroll-face",
        summary: "Enroll employee face data for recognition",
        security: [["sanctum" => []]],
        tags: ["HRM Face Recognition"],
        parameters: [
            new OA\Parameter(name: "employee", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["face_image"],
                    properties: [
                        new OA\Property(property: "face_image", type: "string", format: "binary", description: "Clear face photo")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Face enrolled successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function enrollFace(Employee $employee, Request $request): JsonResponse
    {
        $request->validate([
            'face_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $result = $this->faceRecognitionService->enrollFace($employee, $request->file('face_image'));

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'path' => $result['path'],
            ], 201);
        }

        return response()->json([
            'message' => $result['message'],
        ], 400);
    }

    #[OA\Delete(
        path: "/api/platform/hrm/employees/{employee}/face",
        summary: "Remove employee face data",
        security: [["sanctum" => []]],
        tags: ["HRM Face Recognition"],
        parameters: [
            new OA\Parameter(name: "employee", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Face data removed successfully")
        ]
    )]
    public function removeFaceData(Employee $employee): JsonResponse
    {
        $employee->update([
            'face_image_path' => null,
            'face_encoding' => null,
        ]);

        return response()->json([
            'message' => 'Face data removed successfully',
        ]);
    }
}
