<?php

namespace App\Domain\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Inventory\Models\InventoryCategory;
use App\Domain\Inventory\Models\InventoryProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Inventory Product Catalog", description: "SKUs and product catalog endpoints")]
class InventoryProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InventoryProduct::with(['category', 'stocks.warehouse'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_uuid')) {
            $query->where('category_uuid', $request->category_uuid);
        }

        $products = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'message' => 'List of inventory products',
            'data'    => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'nullable|string|max:100|unique:inventory_products,sku',
            'barcode'       => 'nullable|string|max:100|unique:inventory_products,barcode',
            'category_uuid' => 'nullable|string|exists:inventory_categories,uuid',
            'uom'           => 'nullable|string|max:50',
            'unit_cost'     => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'min_stock'     => 'nullable|integer|min:0',
            'max_stock'     => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
        ]);

        $product = InventoryProduct::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'data'    => $product->load('category'),
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $product = InventoryProduct::with(['category', 'stocks.warehouse', 'movements.warehouse'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'message' => 'Product details',
            'data'    => $product,
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $product = InventoryProduct::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'category_uuid' => 'nullable|string|exists:inventory_categories,uuid',
            'uom'           => 'nullable|string|max:50',
            'unit_cost'     => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'min_stock'     => 'nullable|integer|min:0',
            'max_stock'     => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'data'    => $product->load('category'),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = InventoryCategory::withCount('products')->get();
        return response()->json(['message' => 'Categories', 'data' => $categories]);
    }
}
