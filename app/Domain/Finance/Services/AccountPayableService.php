<?php

namespace App\Domain\Finance\Services;

use App\Domain\Finance\Models\ApBill;
use App\Domain\Finance\Models\ApPayment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AccountPayableService
{
    public function __construct(
        protected MidtransDisbursementService $midtrans
    ) {}

    /**
     * Approve a bill. Must be in 'draft' status.
     */
    public function approveBill(ApBill $bill, User $approver): ApBill
    {
        if ($bill->status !== 'draft') {
            throw new Exception("Only draft bills can be approved. Current status: {$bill->status}");
        }

        $bill->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $bill->fresh(['vendor', 'items', 'approvedBy']);
    }

    /**
     * Pay a bill (partial or full) via Midtrans Iris disbursement or manual.
     */
    public function payBill(ApBill $bill, User $payer, float $amount, string $method = 'midtrans_iris', ?string $notes = null): ApPayment
    {
        if (!in_array($bill->status, ['approved', 'partial'])) {
            throw new Exception("Bill must be in 'approved' or 'partial' status to process payment.");
        }

        $outstanding = (float) $bill->total_amount - (float) $bill->paid_amount;

        if ($amount > $outstanding + 0.01) {
            throw new Exception("Payment amount ({$amount}) exceeds outstanding balance ({$outstanding}).");
        }

        return DB::transaction(function () use ($bill, $payer, $amount, $method, $notes) {
            $payment = ApPayment::create([
                'ap_bill_id'     => $bill->id,
                'payment_date'   => today(),
                'amount'         => $amount,
                'payment_method' => $method,
                'midtrans_status'=> $method === 'midtrans_iris' ? 'pending' : null,
                'created_by'     => $payer->id,
                'notes'          => $notes,
            ]);

            // Trigger Midtrans Iris disbursement
            if ($method === 'midtrans_iris') {
                $this->midtrans->disburse($payment, $bill->vendor);
            }

            // Update bill paid_amount & status
            $newPaid = (float) $bill->paid_amount + $amount;
            $bill->increment('paid_amount', $amount);

            $newStatus = ($newPaid >= (float) $bill->total_amount - 0.01) ? 'paid' : 'partial';
            $bill->update(['status' => $newStatus]);

            return $payment->fresh();
        });
    }

    /**
     * Re-sync the disbursement status from Midtrans Iris.
     */
    public function reconcilePayment(ApPayment $payment): ApPayment
    {
        if (!$payment->midtrans_reference_no) {
            throw new Exception("No Midtrans reference number to reconcile.");
        }

        $data = $this->midtrans->checkStatus($payment->midtrans_reference_no);

        $midtransStatus = match ($data['status'] ?? '') {
            'completed' => 'processed',
            'failed'    => 'failed',
            default     => $payment->midtrans_status,
        };

        $payment->update([
            'midtrans_status'   => $midtransStatus,
            'midtrans_response' => $data,
        ]);

        return $payment->fresh();
    }
}
