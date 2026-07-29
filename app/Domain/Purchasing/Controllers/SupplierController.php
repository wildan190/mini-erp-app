<?php

namespace App\Domain\Purchasing\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Purchasing\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Purchasing - Suppliers", description: "Supplier master data management")]
class SupplierController extends Controller
{
    #[OA\Get(
        path: "/api/platform/purchasing/suppliers",
        summary: "List all suppliers",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Suppliers"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", description: "Search by name or code", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [new OA\Response(response: 200, description: "List of suppliers")]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query();
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%$term%")
                  ->orWhere('code', 'like', "%$term%");
            });
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        return response()->json(['message' => 'List of suppliers', 'data' => $query->orderBy('name')->paginate(15)]);
    }

    #[OA\Post(
        path: "/api/platform/purchasing/suppliers",
        summary: "Create new supplier",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Suppliers"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "PT Jaya Abadi"),
                    new OA\Property(property: "code", type: "string", example: "SUP-001"),
                    new OA\Property(property: "pic", type: "string"),
                    new OA\Property(property: "contact", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "address", type: "string"),
                    new OA\Property(property: "npwp", type: "string"),
                    new OA\Property(property: "category", type: "string"),
                    new OA\Property(property: "currency_code", type: "string", example: "IDR"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Supplier created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50|unique:suppliers,code',
            'pic'           => 'nullable|string|max:255',
            'contact'       => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'npwp'          => 'nullable|string|max:255',
            'category'      => 'nullable|string|max:100',
            'currency_code' => 'nullable|string|max:3',
        ]);

        $supplier = Supplier::create($validated);
        return response()->json(['message' => 'Supplier created successfully', 'data' => $supplier], 201);
    }

    #[OA\Get(
        path: "/api/platform/purchasing/suppliers/{uuid}",
        summary: "Get supplier detail",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Suppliers"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [
            new OA\Response(response: 200, description: "Supplier detail"),
            new OA\Response(response: 404, description: "Supplier not found")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();
        return response()->json(['message' => 'Supplier detail', 'data' => $supplier]);
    }

    #[OA\Put(
        path: "/api/platform/purchasing/suppliers/{uuid}",
        summary: "Update supplier",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Suppliers"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "pic", type: "string"),
                    new OA\Property(property: "contact", type: "string"),
                    new OA\Property(property: "address", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Supplier updated")]
    )]
    public function update(Request $request, string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'pic'           => 'nullable|string|max:255',
            'contact'       => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'npwp'          => 'nullable|string|max:255',
            'category'      => 'nullable|string|max:100',
            'currency_code' => 'nullable|string|max:3',
            'is_active'     => 'sometimes|boolean',
        ]);
        $supplier->update($validated);
        return response()->json(['message' => 'Supplier updated successfully', 'data' => $supplier]);
    }

    #[OA\Delete(
        path: "/api/platform/purchasing/suppliers/{uuid}",
        summary: "Delete supplier",
        security: [["sanctum" => []]],
        tags: ["Purchasing - Suppliers"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 200, description: "Supplier deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}
