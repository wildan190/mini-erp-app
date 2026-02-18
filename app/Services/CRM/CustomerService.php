<?php

namespace App\Services\CRM;

use App\Models\CRM\Customer;
use Illuminate\Support\Str;

class CustomerService
{
    public function index()
    {
        return Customer::latest()->paginate(10);
    }

    public function show($id): Customer
    {
        if (is_numeric($id)) {
            return Customer::findOrFail($id);
        }
        if (Str::isUuid($id)) {
            return Customer::where('uuid', $id)->firstOrFail();
        }
        abort(404);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update($id, array $data): Customer
    {
        if (is_numeric($id)) {
            $customer = Customer::findOrFail($id);
        } elseif (Str::isUuid($id)) {
            $customer = Customer::where('uuid', $id)->firstOrFail();
        } else {
            abort(404);
        }
        $customer->update($data);
        return $customer;
    }

    public function delete($id): bool
    {
        if (is_numeric($id)) {
            $customer = Customer::findOrFail($id);
        } elseif (Str::isUuid($id)) {
            $customer = Customer::where('uuid', $id)->firstOrFail();
        } else {
            abort(404);
        }
        return $customer->delete();
    }
}