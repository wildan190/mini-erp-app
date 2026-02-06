<?php

namespace App\Services\CRM;


use App\Models\Quotation;


class QuotationService
{

    public function index()
    {
        return Quotation::paginate(10);
    }

    public function create(array $data): Quotation
    {
        return Quotation::create($data);
    }
}