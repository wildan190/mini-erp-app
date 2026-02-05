<?php

namespace App\Services\CRM;


use App\Models\Customer;


class CustomerService
{
    public function create(array $data): Customer
    {
        return Customer::create($data);
    }
}