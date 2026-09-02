<?php

namespace App\Domain\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceSettingsController extends Controller
{
    private const MIDTRANS_GROUP = 'midtrans';

    private const MIDTRANS_FIELDS = [
        'midtrans_iris.api_key' => [
            'label'     => 'Midtrans Iris API Key',
            'is_secret' => true,
        ],
        'midtrans_iris.merchant_key' => [
            'label'     => 'Midtrans Iris Merchant Key',
            'is_secret' => true,
        ],
        'midtrans_iris.base_url' => [
            'label'     => 'Iris API Base URL',
            'is_secret' => false,
        ],
    ];

    /**
     * Get current Midtrans settings (secrets are masked).
     */
    public function getMidtransSettings(): JsonResponse
    {
        // Ensure all keys exist in DB (seeded as empty if missing)
        foreach (self::MIDTRANS_FIELDS as $key => $meta) {
            if (!SystemSetting::where('key', $key)->exists()) {
                SystemSetting::set($key, null, self::MIDTRANS_GROUP, $meta['is_secret'], $meta['label']);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => SystemSetting::getGroup(self::MIDTRANS_GROUP),
        ]);
    }

    /**
     * Save Midtrans settings.
     * Empty values (or unchanged "••••" masks) are ignored.
     */
    public function saveMidtransSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'midtrans_iris.api_key'      => 'nullable|string',
            'midtrans_iris.merchant_key' => 'nullable|string',
            'midtrans_iris.base_url'     => 'nullable|url',
        ]);

        foreach ($data as $key => $value) {
            // Skip if user left the field empty or it's a masked placeholder
            if (blank($value) || $value === '••••••••') continue;

            $meta = self::MIDTRANS_FIELDS[$key] ?? ['is_secret' => false, 'label' => $key];

            SystemSetting::set(
                $key,
                $value,
                self::MIDTRANS_GROUP,
                $meta['is_secret'],
                $meta['label']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Midtrans settings saved successfully.',
            'data'    => SystemSetting::getGroup(self::MIDTRANS_GROUP),
        ]);
    }

    /**
     * Test Midtrans connection by hitting the balance endpoint.
     */
    public function testMidtransConnection(): JsonResponse
    {
        $service = app(\App\Domain\Finance\Services\MidtransDisbursementService::class);
        $result  = $service->getBalance();

        $ok = isset($result['balance']) || (isset($result['active_balance']) && !isset($result['error']));

        return response()->json([
            'success'     => $ok,
            'message'     => $ok ? 'Connection successful!' : 'Connection failed. Check your API key.',
            'raw_response'=> $result,
        ]);
    }
}
