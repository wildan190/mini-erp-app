<?php

namespace App\Services\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\Resignation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ResignationService
{
    /**
     * Get resignations with filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getResignations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Resignation::with(['employee.user', 'handoverTo.user'])
            ->when(isset($filters['employee_id']), function ($query) use ($filters) {
                $query->where('employee_id', $filters['employee_id']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Submit a resignation request.
     *
     * @param array $data
     * @return Resignation
     */
    public function submitResignation(array $data): Resignation
    {
        return Resignation::create($data);
    }

    /**
     * Update resignation status.
     *
     * @param Resignation $resignation
     * @param string $status
     * @param string|null $remarks
     * @return Resignation
     */
    public function updateStatus(Resignation $resignation, string $status, ?string $remarks = null): Resignation
    {
        $resignation->update([
            'status' => $status,
            'remarks' => $remarks,
        ]);

        if ($status === 'approved') {
            // Optionally mark employee as 'resigned' or 'terminated' in Employee table immediately
            // Or this could be done via a scheduled job on the resignation_date
            // For now, we'll just update the status if the date is today or past
            if ($resignation->resignation_date <= now()) {
                $resignation->employee->update(['status' => 'resigned']);
            }
        }

        return $resignation;
    }

    /**
     * Withdraw a resignation.
     *
     * @param Resignation $resignation
     * @return Resignation
     */
    public function withdraw(Resignation $resignation): Resignation
    {
        $resignation->update(['status' => 'withdrawn']);
        return $resignation;
    }
    /**
     * Find resignation by ID or UUID.
     *
     * @param string|int $id
     * @return Resignation|null
     */
    public function findResignation(string|int $id): ?Resignation
    {
        if (is_numeric($id)) {
            return Resignation::with(['employee.user', 'handoverTo.user'])->find($id);
        }
        if (Str::isUuid($id)) {
            return Resignation::with(['employee.user', 'handoverTo.user'])->where('uuid', $id)->first();
        }
        return null;
    }
}
