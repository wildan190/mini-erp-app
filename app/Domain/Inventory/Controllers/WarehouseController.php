<?php

namespace App\Domain\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Inventory\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Inventory Warehouse", description: "Multi-warehouse management endpoints")]
class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $warehouses = Warehouse::withCount('stocks')
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'List of warehouses',
            'data'    => $warehouses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'nullable|string|max:50|unique:inventory_warehouses,code',
            'location' => 'nullable|string|max:255',
            'address'  => 'nullable|string',
        ]);

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'message' => 'Warehouse created successfully',
            'data'    => $warehouse,
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $warehouse = Warehouse::with(['stocks.product'])->where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'message' => 'Warehouse detail',
            'data'    => $warehouse,
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $warehouse = Warehouse::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'location'  => 'nullable|string|max:255',
            'address'   => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'message' => 'Warehouse updated successfully',
            'data'    => $warehouse,
        ]);
    }
}
