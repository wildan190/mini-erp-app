<?php

namespace App\Domain\HRM\Services;

use App\Domain\HRM\Models\Attendance;
use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\OfficeLocation;
use App\Domain\HRM\Models\Shift;
use App\Domain\HRM\Jobs\ProcessAttendanceVerification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Domain\HRM\Contracts\HRMServiceInterface;

class AttendanceService implements HRMServiceInterface
{
    protected FaceRecognitionService $faceRecognitionService;
    protected EmployeeService $employeeService;

    public function __construct(
        FaceRecognitionService $faceRecognitionService,
        EmployeeService $employeeService
    ) {
        $this->faceRecognitionService = $faceRecognitionService;
        $this->employeeService = $employeeService;
    }

    public function getEmployeeByUuid(string $uuid): ?Employee
    {
        return Employee::where('uuid', $uuid)->first();
    }
    /**
     * Get attendances with filtering and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAttendances(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Attendance::with(['employee.user', 'shift'])
            ->when(!empty($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->whereHas('employee.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['employee_id']), function (Builder $query) use ($filters) {
                $query->where('employee_id', $filters['employee_id']);
            })
            ->when(!empty($filters['employee_uuid']), function (Builder $query) use ($filters) {
                $employee = Employee::where('uuid', $filters['employee_uuid'])->first();
                $query->where('employee_id', $employee?->id ?? 0);
            })
            ->when(!empty($filters['date']), function (Builder $query) use ($filters) {
                $query->where('date', $filters['date']);
            })
            ->when(!empty($filters['department_uuid']), function (Builder $query) use ($filters) {
                $department = \App\Domain\HRM\Models\Department::where('uuid', $filters['department_uuid'])->first();
                $query->whereHas('employee', function ($q) use ($department) {
                    $q->where('department_id', $department?->id ?? 0);
                });
            })
            ->latest('date')
            ->paginate($perPage);
    }

    /**
     * Clock in for an employee - Creates attendance immediately and verifies asynchronously.
     *
     * @param Employee $employee
     * @param array $data
     * @return Attendance
     * @throws \Exception
     */
    public function clockIn(Employee $employee, array $data): Attendance
    {
        $today = Carbon::today();

        // Check if already clocked in today
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->first();

        if ($existingAttendance) {
            throw new \Exception('Employee already clocked in for today.');
        }

        // Validate face image requirement
        if ($employee->requires_face_verification && empty($data['face_image'])) {
            throw new \Exception('Face verification is required but no face image provided.');
        }

        // Check if face is enrolled (only required when face verification is enabled)
        if ($employee->requires_face_verification && empty($employee->face_encoding)) {
            throw new \Exception('Anda belum mendaftarkan wajah. Silakan daftarkan wajah Anda terlebih dahulu melalui menu profil atau hubungi admin.');
        }

        // Store temporary face image for async verification
        $tempFaceImagePath = null;
        $hasFaceImage = isset($data['face_image']);
        $isUploadedFile = $hasFaceImage && ($data['face_image'] instanceof UploadedFile);
        \Illuminate\Support\Facades\Log::info('AttendanceService clockIn face_image check', [
            'hasFaceImage' => $hasFaceImage,
            'isUploadedFile' => $isUploadedFile,
            'type' => $hasFaceImage ? get_debug_type($data['face_image']) : 'none',
        ]);
        if ($isUploadedFile) {
            $tempFaceImagePath = $data['face_image']->store('faces/temp', 'public');
            \Illuminate\Support\Facades\Log::info('tempFaceImagePath result', ['path' => $tempFaceImagePath]);
        }

        // Get location data
        $officeLocationId = $data['office_location_id'] ?? null;
        if (isset($data['office_location_uuid'])) {
            $officeLoc = OfficeLocation::where('uuid', $data['office_location_uuid'])->first();
            $officeLocationId = $officeLoc?->id;
        } else {
            $officeLoc = OfficeLocation::find($officeLocationId);
        }
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        // Synchronous distance check - set final location status immediately
        $locationVerificationStatus = 'skipped';
        if ($officeLoc && $latitude && $longitude) {
            $distance = $this->calculateDistance($latitude, $longitude, $officeLoc->latitude, $officeLoc->longitude);
            if ($distance > $officeLoc->radius) {
                throw new \Exception('You are outside the office radius.');
            }
            $locationVerificationStatus = 'within_radius';
        }

        // Determine initial status
        $shift = $employee->shift;
        $status = $this->determineStatus($shift, Carbon::now());

        // Create attendance record with verified location status (location is already validated above)
        $attendance = Attendance::create([
            'employee_id'               => $employee->id,
            'shift_id'                  => $shift?->id,
            'date'                      => $today->toDateString(),
            'clock_in'                  => Carbon::now(),
            'status'                    => $status,
            'location_lat'              => $data['location_lat'] ?? null,
            'location_long'             => $data['location_long'] ?? null,
            'notes'                     => $data['notes'] ?? null,
            // Location is validated synchronously above; face is async via job
            'face_verification_status'     => $tempFaceImagePath ? 'pending' : 'skipped',
            'location_verification_status' => $locationVerificationStatus,
            'office_location_id'           => $officeLocationId,
            'check_in_latitude'            => $latitude,
            'check_in_longitude'           => $longitude,
        ]);

        // Dispatch face verification job (direct execution if running unit tests, async via Redis in production)
        if ($tempFaceImagePath) {
            if (app()->runningUnitTests() || config('queue.default') === 'sync') {
                (new ProcessAttendanceVerification(
                    $attendance,
                    $tempFaceImagePath,
                    $officeLocationId,
                    $latitude,
                    $longitude,
                    'clock_in'
                ))->handle(app(FaceRecognitionService::class));
                $attendance->refresh();
            } else {
                ProcessAttendanceVerification::dispatch(
                    $attendance,
                    $tempFaceImagePath,
                    $officeLocationId,
                    $latitude,
                    $longitude,
                    'clock_in'
                );
            }
        }

        return $attendance;
    }

    /**
     * Clock out for an employee - Updates attendance and verifies asynchronously.
     *
     * @param Employee $employee
     * @param array $data
     * @return Attendance
     * @throws \Exception
     */
    public function clockOut(Employee $employee, array $data): Attendance
    {
        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->first();

        if (!$attendance) {
            throw new \Exception('No attendance record found for today. Please clock in first.');
        }

        if ($attendance->clock_out) {
            throw new \Exception('Employee already clocked out for today.');
        }

        // Store temporary face image for async verification (optional for clock-out)
        $tempFaceImagePath = null;
        if (isset($data['face_image']) && $data['face_image'] instanceof UploadedFile) {
            $tempFaceImagePath = $data['face_image']->store('faces/temp', 'public');
        }

        // Get location data
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        // Update attendance record immediately
        $attendance->update([
            'clock_out' => Carbon::now(),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'notes' => $attendance->notes . ($data['notes'] ? "\nClock Out Note: " . $data['notes'] : ''),
        ]);

        // Dispatch async verification job for clock-out if needed
        if ($tempFaceImagePath || ($attendance->office_location_id && $latitude && $longitude)) {
            ProcessAttendanceVerification::dispatch(
                $attendance,
                $tempFaceImagePath,
                $attendance->office_location_id,
                $latitude,
                $longitude,
                'clock_out'
            );
        }

        return $attendance;
    }

    /**
     * Determine attendance status based on shift and time.
     *
     * @param Shift|null $shift
     * @param Carbon $time
     * @return string
     */
    protected function determineStatus(?Shift $shift, Carbon $time): string
    {
        if (!$shift) {
            return 'present'; // ongoing/present
        }

        // Compare clock in time with shift start time + grace period (e.g., 15 mins)
        // This logic can be refined based on business rules
        $shiftStart = Carbon::parse($shift->start_time);

        if ($time->format('H:i') > $shiftStart->addMinutes(15)->format('H:i')) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Calculate distance between two coordinate points in meters using Haversine formula
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
