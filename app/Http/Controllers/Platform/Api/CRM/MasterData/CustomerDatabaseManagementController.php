<?php

namespace App\Http\Controllers\Platform\Api\CRM\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\MasterData\CustomerRequest;
use App\Services\CRM\CustomerService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Customers", description: "API Endpoints for Customer Management")]
class CustomerDatabaseManagementController extends Controller
{
    #[OA\Get(
        path: "/api/platform/crm/customers",
        summary: "List all customers",
        security: [["sanctum" => []]],
        tags: ["Customers"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(CustomerService $service)
    {
        return response()->json([
            'message' => 'List customer database',
            'data' => $service->index()
        ]);
    }

    #[OA\Get(
        path: "/api/platform/crm/customers/{uuid}",
        summary: "Get customer detail",
        security: [["sanctum" => []]],
        tags: ["Customers"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Customer not found")
        ]
    )]
    public function show($id, CustomerService $service)
    {
        return response()->json([
            'message' => 'Detail customer',
            'data' => $service->show($id)
        ]);
    }

    #[OA\Post(
        path: "/api/platform/crm/customers",
        summary: "Create new customer",
        security: [["sanctum" => []]],
        tags: ["Customers"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["name", "email", "customer_type", "status"],
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Acme Corp"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "contact@acme.com"),
                        new OA\Property(property: "company_name", type: "string", example: "Acme Corporation Ltd."),
                        new OA\Property(property: "customer_type", type: "string", enum: ["corporate", "individual"]),
                        new OA\Property(property: "tax_id", type: "string", example: "12.345.678.9-012.000"),
                        new OA\Property(property: "industry", type: "string"),
                        new OA\Property(property: "website", type: "string", format: "url"),
                        new OA\Property(property: "phone", type: "string"),
                        new OA\Property(property: "alt_phone", type: "string"),
                        new OA\Property(property: "department", type: "string"),
                        new OA\Property(property: "billing_address", type: "string"),
                        new OA\Property(property: "shipping_address", type: "string"),
                        new OA\Property(property: "city", type: "string"),
                        new OA\Property(property: "province", type: "string"),
                        new OA\Property(property: "postal_code", type: "string"),
                        new OA\Property(property: "country", type: "string"),
                        new OA\Property(property: "credit_limit", type: "number"),
                        new OA\Property(property: "payment_terms", type: "string"),
                        new OA\Property(property: "currency", type: "string", maxLength: 3),
                        new OA\Property(property: "segment", type: "string"),
                        new OA\Property(property: "status", type: "string", enum: ["active", "inactive", "blocked"]),
                        new OA\Property(property: "notes", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Customer created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(CustomerRequest $request, CustomerService $service)
    {
        $customer = $service->create($request->validated());

        return response()->json([
            'message' => 'Customer berhasil ditambahkan',
            'data' => $customer
        ], 201);
    }

    #[OA\Put(
        path: "/api/platform/crm/customers/{uuid}",
        summary: "Update existing customer",
        security: [["sanctum" => []]],
        tags: ["Customers"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string", format: "email"),
                        new OA\Property(property: "company_name", type: "string"),
                        new OA\Property(property: "customer_type", type: "string", enum: ["corporate", "individual"]),
                        new OA\Property(property: "tax_id", type: "string"),
                        new OA\Property(property: "industry", type: "string"),
                        new OA\Property(property: "website", type: "string", format: "url"),
                        new OA\Property(property: "phone", type: "string"),
                        new OA\Property(property: "alt_phone", type: "string"),
                        new OA\Property(property: "department", type: "string"),
                        new OA\Property(property: "billing_address", type: "string"),
                        new OA\Property(property: "shipping_address", type: "string"),
                        new OA\Property(property: "city", type: "string"),
                        new OA\Property(property: "province", type: "string"),
                        new OA\Property(property: "postal_code", type: "string"),
                        new OA\Property(property: "country", type: "string"),
                        new OA\Property(property: "credit_limit", type: "number"),
                        new OA\Property(property: "payment_terms", type: "string"),
                        new OA\Property(property: "currency", type: "string", maxLength: 3),
                        new OA\Property(property: "segment", type: "string"),
                        new OA\Property(property: "status", type: "string", enum: ["active", "inactive", "blocked"]),
                        new OA\Property(property: "notes", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Customer updated"),
            new OA\Response(response: 404, description: "Customer not found")
        ]
    )]
    public function update($id, CustomerRequest $request, CustomerService $service)
    {
        $customer = $service->update($id, $request->validated());

        return response()->json([
            'message' => 'Customer berhasil diperbarui',
            'data' => $customer
        ]);
    }

    #[OA\Delete(
        path: "/api/platform/crm/customers/{uuid}",
        summary: "Delete customer",
        security: [["sanctum" => []]],
        tags: ["Customers"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Customer deleted"),
            new OA\Response(response: 404, description: "Customer not found")
        ]
    )]
    public function destroy($id, CustomerService $service)
    {
        $service->delete($id);

        return response()->json([
            'message' => 'Customer berhasil dihapus'
        ]);
    }
}