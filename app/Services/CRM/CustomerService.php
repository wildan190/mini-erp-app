<?php

namespace App\Services\CRM;

use App\Models\CRM\Customer;

class CustomerService
{
    public function index()
    {
        return Customer::latest()->paginate(10);
    }

    public function show($id): Customer
    {
        return Customer::where('uuid', $id)->firstOrFail();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update($id, array $data): Customer
    {
        $customer = Customer::where('uuid', $id)->firstOrFail();
        $customer->update($data);
        return $customer;
    }

    public function delete($id): bool
    {
        return Customer::where('uuid', $id)->firstOrFail()->delete();
    }
}