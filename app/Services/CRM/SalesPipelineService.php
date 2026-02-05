<?php

namespace App\Services\CRM;


use App\Models\SalesPipeline;


class SalesPipelineService
{
    public function create(array $data): SalesPipeline
    {
        return SalesPipeline::create($data);
    }
}