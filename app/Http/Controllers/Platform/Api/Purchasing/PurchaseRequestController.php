<?php

namespace App\Http\Controllers\Platform\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseRequest;
use App\Models\Purchasing\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $requests = PurchaseRequest::with(['requestor', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $requests]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'department_uuid' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $pr = PurchaseRequest::create([
                'number' => 'PR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'date' => $validated['date'],
                'requestor_id' => $request->user()->id,
                'department_uuid' => $validated['department_uuid'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $pr->items()->create($item);
            }

            return response()->json(['success' => true, 'data' => $pr->load('items')], 201);
        });
    }

    public function show($uuid)
    {
        $pr = PurchaseRequest::with(['requestor', 'items'])->where('uuid', $uuid)->firstOrFail();
        return response()->json(['success' => true, 'data' => $pr]);
    }

    public function updateStatus(Request $request, $uuid)
    {
        $pr = PurchaseRequest::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $pr->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'data' => $pr]);
    }
}
