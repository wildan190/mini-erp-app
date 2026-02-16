<?php

namespace App\Services\HRM;

use App\Models\HRM\Attendance;
use App\Models\HRM\Employee;
use App\Models\HRM\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceService
{
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
            ->when(isset($filters['employee_id']), function (Builder $query) use ($filters) {
                $query->where('employee_id', $filters['employee_id']);
            })
            ->when(isset($filters['date']), function (Builder $query) use ($filters) {
                $query->whereDate('date', $filters['date']);
            })
            ->when(isset($filters['department_id']), function (Builder $query) use ($filters) {
                $query->whereHas('employee', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->latest('date')
            ->paginate($perPage);
    }

    /**
     * Clock in for an employee.
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
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            throw new \Exception('Employee already clocked in for today.');
        }

        $shift = $employee->shift;
        $status = $this->determineStatus($shift, Carbon::now());

        return Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift?->id,
            'date' => $today,
            'clock_in' => Carbon::now(),
            'status' => $status,
            'location_lat' => $data['location_lat'] ?? null,
            'location_long' => $data['location_long'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Clock out for an employee.
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
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            throw new \Exception('No attendance record found for today. Please clock in first.');
        }

        if ($attendance->clock_out) {
            throw new \Exception('Employee already clocked out for today.');
        }

        $attendance->update([
            'clock_out' => Carbon::now(),
            'notes' => $attendance->notes . ($data['notes'] ? "\nClock Out Note: " . $data['notes'] : ''),
        ]);

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
