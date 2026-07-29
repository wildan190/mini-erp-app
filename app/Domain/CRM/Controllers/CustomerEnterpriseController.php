<?php

namespace App\Domain\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\CRM\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "CRM - Enterprise", description: "Enterprise/corporate customer registration")]
class CustomerEnterpriseController extends Controller
{
    #[OA\Post(
        path: "/api/platform/crm/customers/enterprise",
        summary: "Register an enterprise (corporate) customer",
        security: [["sanctum" => []]],
        tags: ["CRM - Enterprise"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "company_name", "email"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "PT Maju Bersama"),
                    new OA\Property(property: "company_name", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "tax_id", type: "string", description: "NPWP"),
                    new OA\Property(property: "industry", type: "string"),
                    new OA\Property(property: "phone", type: "string"),
                    new OA\Property(property: "billing_address", type: "string"),
                    new OA\Property(property: "credit_limit", type: "number"),
                    new OA\Property(property: "currency", type: "string", example: "IDR"),
                    new OA\Property(property: "notes", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Enterprise customer created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:customers,email',
            'tax_id'          => 'nullable|string|max:100',
            'industry'        => 'nullable|string|max:100',
            'website'         => 'nullable|url|max:255',
            'phone'           => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'province'        => 'nullable|string|max:100',
            'credit_limit'    => 'nullable|numeric|min:0',
            'currency'        => 'nullable|string|max:3',
            'notes'           => 'nullable|string',
        ]);

        $customer = Customer::create(array_merge($validated, [
            'customer_type' => 'corporate',
            'status'        => 'active',
        ]));

        return response()->json(['message' => 'Enterprise customer created successfully', 'data' => $customer], 201);
    }
}
