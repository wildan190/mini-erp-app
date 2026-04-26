<?php

namespace App\Http\Controllers\Platform\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account;
use App\Models\Finance\JournalItem;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance Ledger", description: "General Ledger and Chart of Accounts Management")]
class GeneralLedgerController extends Controller
{
    #[OA\Get(
        path: "/api/platform/finance/ledger/accounts",
        summary: "List Chart of Accounts",
        security: [["sanctum" => []]],
        tags: ["Finance Ledger"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function accounts(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Account::with('parent')->get()
        ]);
    }

    #[OA\Get(
        path: "/api/platform/finance/ledger/items",
        summary: "List General Ledger Items",
        security: [["sanctum" => []]],
        tags: ["Finance Ledger"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function items(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => JournalItem::with(['account', 'entry'])->latest()->paginate(20)
        ]);
    }
}
