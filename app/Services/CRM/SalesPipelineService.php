<?php

namespace App\Services\CRM;


use App\Models\SalesPipeline;


class SalesPipelineService
{

    public function index()
    {
        return SalesPipeline::paginate(10);
    }

    public function create(array $data): SalesPipeline
    {
        return SalesPipeline::create($data);
    }
}