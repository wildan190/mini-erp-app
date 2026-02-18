<?php

namespace App\Services\HRM;

use App\Models\HRM\Attendance;
use App\Models\HRM\Employee;
use App\Models\HRM\OfficeLocation;
use App\Models\HRM\Shift;
use App\Jobs\HRM\ProcessAttendanceVerification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceService
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
            ->when(isset($filters['employee_uuid']), function (Builder $query) use ($filters) {
                $employee = Employee::where('uuid', $filters['employee_uuid'])->first();
                $query->where('employee_id', $employee?->id ?? 0);
            })
            ->when(isset($filters['date']), function (Builder $query) use ($filters) {
                $query->whereDate('date', $filters['date']);
            })
            ->when(isset($filters['department_uuid']), function (Builder $query) use ($filters) {
                $department = \App\Models\HRM\Department::where('uuid', $filters['department_uuid'])->first();
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

        // Store temporary face image for async verification
        $tempFaceImagePath = null;
        if (isset($data['face_image']) && $data['face_image'] instanceof UploadedFile) {
            $tempFaceImagePath = $data['face_image']->store('faces/temp', 'public');
        }

        // Get location data
        $officeLocationId = $data['office_location_id'] ?? null;
        if (isset($data['office_location_uuid'])) {
            $officeLocationId = OfficeLocation::where('uuid', $data['office_location_uuid'])->value('id');
        }
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        // Determine initial status
        $shift = $employee->shift;
        $status = $this->determineStatus($shift, Carbon::now());

        // Create attendance record immediately with pending verification status
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift?->id,
            'date' => $today,
            'clock_in' => Carbon::now(),
            'status' => $status,
            'location_lat' => $data['location_lat'] ?? null,
            'location_long' => $data['location_long'] ?? null,
            'notes' => $data['notes'] ?? null,
            // Set as pending - will be updated by job
            'face_verification_status' => $tempFaceImagePath ? 'pending' : 'skipped',
            'location_verification_status' => ($officeLocationId && $latitude && $longitude) ? 'pending' : 'skipped',
            'office_location_id' => $officeLocationId,
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
        ]);

        // Dispatch async verification job if needed
        if ($tempFaceImagePath || ($officeLocationId && $latitude && $longitude)) {
            ProcessAttendanceVerification::dispatch(
                $attendance,
                $tempFaceImagePath,
                $officeLocationId,
                $latitude,
                $longitude,
                'clock_in'
            );
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
}
