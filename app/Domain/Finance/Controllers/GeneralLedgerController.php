<?php

namespace App\Domain\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Finance\Models\Account;
use App\Domain\Finance\Models\JournalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance Ledger", description: "General Ledger and Chart of Accounts Management")]
class GeneralLedgerController extends Controller
{
    public function accounts(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Account::with('parent')->orderBy('code')->get()
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'             => ['required', 'string', 'max:50', Rule::unique('accounts', 'code')->whereNull('deleted_at')],
            'name'             => ['required', 'string', 'max:255'],
            'type'             => ['required', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'parent_uuid'      => ['nullable', 'string', 'exists:accounts,uuid'],
            'is_reconcilable'  => ['boolean'],
        ]);

        $account = Account::create($validated);
        $account->load('parent');

        return response()->json([
            'status'  => 'success',
            'message' => 'Account created successfully.',
            'data'    => $account,
        ], 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $account = Account::findOrFail($uuid);

        $validated = $request->validate([
            'code'             => ['sometimes', 'string', 'max:50', Rule::unique('accounts', 'code')->ignore($account->uuid, 'uuid')->whereNull('deleted_at')],
            'name'             => ['sometimes', 'string', 'max:255'],
            'type'             => ['sometimes', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'parent_uuid'      => ['nullable', 'string', 'exists:accounts,uuid'],
            'is_reconcilable'  => ['boolean'],
        ]);

        $account->update($validated);
        $account->load('parent');

        return response()->json([
            'status'  => 'success',
            'message' => 'Account updated successfully.',
            'data'    => $account,
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $account = Account::findOrFail($uuid);
        $account->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Account deleted.',
        ]);
    }

    public function items(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => JournalItem::with(['account', 'entry'])->latest()->paginate(20)
        ]);
    }
}
