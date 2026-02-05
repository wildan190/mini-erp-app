<?php

namespace App\Http\Controllers\Platform\Api\CRM\MasterData;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\MasterData\CustomerDatabaseManagementRequest;
use App\Models\Customer;


class CustomerDatabaseManagementController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'List customer database'
        ]);
    }

    public function store(CustomerDatabaseManagementRequest $request)
    {
        $customer = Customer::create($request->validated());

        return response()->json([
            'message' => 'Customer berhasil ditambahkan',
            'data' => $customer
        ], 201);
    }

}