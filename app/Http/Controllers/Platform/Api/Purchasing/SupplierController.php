<?php

namespace App\Http\Controllers\Platform\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pic' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'currency_code' => 'nullable|string|max:3',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'success' => true,
            'data' => $supplier
        ], 201);
    }

    public function show($uuid)
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();
        return response()->json([
            'success' => true,
            'data' => $supplier
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'pic' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'currency_code' => 'nullable|string|max:3',
            'is_active' => 'sometimes|boolean',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'data' => $supplier
        ]);
    }

    public function destroy($uuid)
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully'
        ]);
    }
}
