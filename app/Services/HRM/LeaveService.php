<?php

namespace App\Services\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\LeaveBalance;
use App\Models\HRM\LeaveRequest;
use App\Models\HRM\LeaveType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaveService
{
    /**
     * Get leave requests with filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeaveRequests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return LeaveRequest::with(['employee.user', 'leaveType', 'approver'])
            ->when(isset($filters['employee_id']), function (Builder $query) use ($filters) {
                $query->where('employee_id', $filters['employee_id']);
            })
            ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Apply for leave.
     *
     * @param Employee $employee
     * @param array $data
     * @return LeaveRequest
     * @throws \Exception
     */
    public function applyForLeave(Employee $employee, array $data): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $daysRequested = $startDate->diffInDays($endDate) + 1; // Inclusive

        // specific logic for weekends/holidays can be added here

        // Check balance
        $year = $startDate->year;
        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $data['leave_type_id'],
                'year' => $year
            ],
            [
                'total_days' => LeaveType::find($data['leave_type_id'])->days_allowed,
                'used_days' => 0,
                'remaining_days' => LeaveType::find($data['leave_type_id'])->days_allowed
            ]
        );

        if ($balance->remaining_days < $daysRequested) {
            throw new \Exception('Insufficient leave balance. Remaining: ' . $balance->remaining_days . ' days.');
        }

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'status' => 'pending'
        ]);
    }

    /**
     * Approve or Reject leave request.
     *
     * @param LeaveRequest $leaveRequest
     * @param string $status
     * @param int $approverId
     * @param string|null $rejectionReason
     * @return LeaveRequest
     * @throws \Exception
     */
    public function updateLeaveStatus(LeaveRequest $leaveRequest, string $status, int $approverId, ?string $rejectionReason = null): LeaveRequest
    {
        if ($leaveRequest->status !== 'pending') {
            throw new \Exception('Leave request is already ' . $leaveRequest->status);
        }

        DB::transaction(function () use ($leaveRequest, $status, $approverId, $rejectionReason) {
            if ($status === 'approved') {
                $startDate = Carbon::parse($leaveRequest->start_date);
                $endDate = Carbon::parse($leaveRequest->end_date);
                $daysRequested = $startDate->diffInDays($endDate) + 1;

                $year = $startDate->year;
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($balance->remaining_days < $daysRequested) {
                    throw new \Exception('Insufficient leave balance.');
                }

                $balance->used_days += $daysRequested;
                $balance->remaining_days -= $daysRequested;
                $balance->save();
            }

            $leaveRequest->update([
                'status' => $status,
                'approved_by' => $approverId,
                'rejection_reason' => $rejectionReason
            ]);
        });

        return $leaveRequest->fresh();
    }

    /**
     * Get leave balance for an employee.
     *
     * @param int $employeeId
     * @param int $year
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmployeeBalance(int $employeeId, int $year)
    {
        // Ensure balances exist for all leave types
        $leaveTypes = LeaveType::all();
        foreach ($leaveTypes as $type) {
            LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $type->id,
                    'year' => $year
                ],
                [
                    'total_days' => $type->days_allowed,
                    'used_days' => 0,
                    'remaining_days' => $type->days_allowed
                ]
            );
        }

        return LeaveBalance::with('leaveType')
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->get();
    }
    /**
     * Find leave request by ID or UUID.
     *
     * @param string|int $id
     * @return LeaveRequest|null
     */
    public function findLeaveRequest(string|int $id): ?LeaveRequest
    {
        if (is_numeric($id)) {
            return LeaveRequest::with(['employee.user', 'leaveType', 'approver'])->find($id);
        }
        if (Str::isUuid($id)) {
            return LeaveRequest::with(['employee.user', 'leaveType', 'approver'])->where('uuid', $id)->first();
        }
        return null;
    }

    /**
     * Find leave type by ID or UUID.
     *
     * @param string|int $id
     * @return LeaveType|null
     */
    public function findLeaveType(string|int $id): ?LeaveType
    {
        if (is_numeric($id)) {
            return LeaveType::find($id);
        }
        if (Str::isUuid($id)) {
            return LeaveType::where('uuid', $id)->first();
        }
        return null;
    }
}
