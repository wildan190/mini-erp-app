<?php

namespace App\Domain\Finance\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Domain\Finance\Models\ApVendor;
use App\Domain\Finance\Models\ApPayment;

class MidtransDisbursementService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $merchantKey;

    public function __construct()
    {
        // Priority: DB settings → .env config → default sandbox URL
        $this->apiKey      = \App\Models\SystemSetting::get('midtrans_iris.api_key')
                          ?: config('services.midtrans_iris.api_key', '');

        $this->merchantKey = \App\Models\SystemSetting::get('midtrans_iris.merchant_key')
                          ?: config('services.midtrans_iris.merchant_key', '');

        $this->baseUrl     = \App\Models\SystemSetting::get('midtrans_iris.base_url')
                          ?: config('services.midtrans_iris.base_url', 'https://app.sandbox.midtrans.com/iris/api/v1');
    }


    protected function headers(): array
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
        ];
    }

    /**
     * Check Iris wallet balance.
     */
    public function getBalance(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/balance");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Midtrans Iris getBalance failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Register a vendor as a beneficiary in Iris.
     * Returns alias name if successful.
     */
    public function createBeneficiary(ApVendor $vendor): array
    {
        $alias = strtolower(preg_replace('/\s+/', '_', $vendor->name)) . '_' . $vendor->uuid;
        $alias = substr($alias, 0, 20); // Iris alias max 20 chars

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/beneficiaries", [
                    'name'        => $vendor->bank_account_name,
                    'account'     => $vendor->bank_account_number,
                    'bank'        => strtolower($vendor->bank_code),
                    'alias_name'  => $alias,
                    'email'       => $vendor->email ?? null,
                ]);

            $data = $response->json();

            if ($response->successful()) {
                $vendor->update(['midtrans_beneficiary_alias' => $alias]);
            }

            return array_merge($data ?? [], ['alias' => $alias]);
        } catch (\Throwable $e) {
            Log::error('Midtrans Iris createBeneficiary failed', [
                'vendor' => $vendor->uuid,
                'error'  => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Send disbursement for a specific payment.
     */
    public function disburse(ApPayment $payment, ApVendor $vendor): array
    {
        $alias = $vendor->midtrans_beneficiary_alias;

        if (!$alias) {
            $result = $this->createBeneficiary($vendor);
            $alias  = $result['alias'] ?? null;
        }

        if (!$alias) {
            return ['error' => 'Failed to register beneficiary. Disbursement aborted.'];
        }

        $referenceNo = 'AP-PAY-' . strtoupper(substr($payment->uuid, 0, 8));

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/payouts", [
                    'payouts' => [
                        [
                            'beneficiary_name'    => $vendor->bank_account_name,
                            'beneficiary_account' => $vendor->bank_account_number,
                            'beneficiary_bank'    => strtolower($vendor->bank_code),
                            'beneficiary_email'   => $vendor->email ?? null,
                            'amount'              => (int) ($payment->amount * 100), // Iris uses cents
                            'notes'               => "AP Payment {$referenceNo}",
                        ]
                    ]
                ]);

            $data = $response->json();

            $status = $response->successful() ? 'queued' : 'failed';

            $payment->update([
                'midtrans_reference_no'        => $referenceNo,
                'midtrans_beneficiary_alias'   => $alias,
                'midtrans_status'              => $status,
                'midtrans_response'            => $data,
            ]);

            Log::info('Midtrans Iris disbursement', [
                'reference' => $referenceNo,
                'status'    => $status,
            ]);

            return $data ?? [];
        } catch (\Throwable $e) {
            Log::error('Midtrans Iris disburse failed', [
                'payment' => $payment->uuid,
                'error'   => $e->getMessage(),
            ]);

            $payment->update([
                'midtrans_status'   => 'failed',
                'midtrans_response' => ['error' => $e->getMessage()],
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Fetch the latest disbursement status from Iris.
     */
    public function checkStatus(string $referenceNo): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payouts/{$referenceNo}");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Midtrans Iris checkStatus failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}
