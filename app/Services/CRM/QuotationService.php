<?php

namespace App\Services\CRM;


use App\Models\Quotation;


class QuotationService
{
    public function create(array $data): Quotation
    {
        return Quotation::create($data);
    }
}