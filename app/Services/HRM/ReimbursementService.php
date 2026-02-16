<?php

namespace App\Services\HRM;

use App\Models\HRM\Reimbursement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ReimbursementService
{
    /**
     * Get reimbursements with filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getReimbursements(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Reimbursement::with(['employee.user', 'approver'])
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
     * Submit a new reimbursement claim.
     *
     * @param array $data
     * @return Reimbursement
     */
    public function submitClaim(array $data): Reimbursement
    {
        if (isset($data['proof_file']) && $data['proof_file'] instanceof \Illuminate\Http\UploadedFile) {
            $path = $data['proof_file']->store('reimbursements', 'public');
            $data['proof_file'] = $path;
        }

        return Reimbursement::create($data);
    }

    /**
     * Update reimbursement status (Approve/Reject).
     *
     * @param Reimbursement $reimbursement
     * @param string $status
     * @param int|null $approverId
     * @param string|null $reason
     * @return Reimbursement
     */
    public function updateStatus(Reimbursement $reimbursement, string $status, ?int $approverId = null, ?string $reason = null): Reimbursement
    {
        $updateData = ['status' => $status];

        if ($status === 'approved') {
            $updateData['approved_by'] = $approverId;
            $updateData['approved_at'] = now();
        } elseif ($status === 'rejected') {
            $updateData['approved_by'] = $approverId; // Use approver field to track who rejected it too
            $updateData['rejection_reason'] = $reason;
        }

        $reimbursement->update($updateData);

        return $reimbursement;
    }

    /**
     * Mark reimbursement as paid.
     *
     * @param Reimbursement $reimbursement
     * @return Reimbursement
     */
    public function markAsPaid(Reimbursement $reimbursement): Reimbursement
    {
        $reimbursement->update(['status' => 'paid']);
        return $reimbursement;
    }
}
