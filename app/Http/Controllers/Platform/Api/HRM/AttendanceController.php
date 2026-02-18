<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\HRM\Attendance\ClockInRequest;
use App\Http\Requests\Platform\HRM\Attendance\ClockOutRequest;
use App\Models\HRM\Employee;
use App\Services\HRM\AttendanceService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "HRM Attendance", description: "API Endpoints for Attendance Management")]
class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    #[OA\Get(
        path: "/api/platform/hrm/attendances",
        summary: "List all attendances",
        security: [["sanctum" => []]],
        tags: ["HRM Attendance"],
        parameters: [
            new OA\Parameter(name: "employee_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "date", in: "query", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "department_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(): JsonResponse
    {
        $filters = request()->only(['employee_id', 'date', 'department_id']);
        $perPage = request()->input('per_page', 15);
        $attendances = $this->attendanceService->getAttendances($filters, $perPage);
        return response()->json([
            'message' => 'List of attendances',
            'data' => $attendances
        ]);
    }

    #[OA\Post(
        path: "/api/platform/hrm/attendances/clock-in",
        summary: "Clock In with face recognition and geofencing",
        security: [["sanctum" => []]],
        tags: ["HRM Attendance"],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "face_image", type: "string", format: "binary", description: "Face photo for verification (if required)"),
                        new OA\Property(property: "office_location_id", type: "integer", description: "Office location ID for geofencing"),
                        new OA\Property(property: "latitude", type: "number", format: "float", description: "Current GPS latitude"),
                        new OA\Property(property: "longitude", type: "number", format: "float", description: "Current GPS longitude"),
                        new OA\Property(property: "location_lat", type: "string", description: "Legacy location latitude"),
                        new OA\Property(property: "location_long", type: "string", description: "Legacy location longitude"),
                        new OA\Property(property: "notes", type: "string", description: "Optional notes")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Clocked in successfully - verification pending"),
            new OA\Response(response: 400, description: "Already clocked in or validation error"),
            new OA\Response(response: 404, description: "Employee record not found")
        ]
    )]
    public function clockIn(ClockInRequest $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->employee) {
            return response()->json(['message' => 'User is not an employee'], 404);
        }

        try {
            $attendance = $this->attendanceService->clockIn($user->employee, $request->validated());
            return response()->json([
                'message' => 'Clocked in successfully',
                'data' => $attendance
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: "/api/platform/hrm/attendances/clock-out",
        summary: "Clock Out with optional face and location verification",
        security: [["sanctum" => []]],
        tags: ["HRM Attendance"],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "face_image", type: "string", format: "binary", description: "Face photo for verification (optional)"),
                        new OA\Property(property: "latitude", type: "number", format: "float", description: "Current GPS latitude"),
                        new OA\Property(property: "longitude", type: "number", format: "float", description: "Current GPS longitude"),
                        new OA\Property(property: "notes", type: "string", description: "Optional notes")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Clocked out successfully"),
            new OA\Response(response: 400, description: "Not clocked in or already clocked out"),
            new OA\Response(response: 404, description: "Employee record not found")
        ]
    )]
    public function clockOut(ClockOutRequest $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->employee) {
            return response()->json(['message' => 'User is not an employee'], 404);
        }

        try {
            $attendance = $this->attendanceService->clockOut($user->employee, $request->validated());
            return response()->json([
                'message' => 'Clocked out successfully',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
