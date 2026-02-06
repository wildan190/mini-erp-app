<?php

namespace App\Http\Controllers\Platform\Api\CRM\MasterData;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\MasterData\CustomerDatabaseManagementRequest;
use App\Services\CRM\CustomerService;


class CustomerDatabaseManagementController extends Controller
{
    public function index(CustomerService $service)
    {
        $customers = $service->index();

        return response()->json([
            'message' => 'List customer database',
            'data' => $customers
        ]);
    }

    public function store(CustomerDatabaseManagementRequest $request, CustomerService $service)
    {
        $customer = $service->create($request->validated());


        return response()->json([
            'message' => 'Customer berhasil ditambahkan',
            'data' => $customer
        ], 201);
    }

}