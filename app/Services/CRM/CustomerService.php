<?php

namespace App\Services\CRM;


use App\Models\Customer;


class CustomerService
{

    public function index()
    {
        return Customer::paginate(10);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }
}