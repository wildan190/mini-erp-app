<?php

namespace App\Services\CRM;

use App\Models\SalesPipeline;

class SalesPipelineService
{
    public function index()
    {
        return SalesPipeline::with('prospect.customer')->latest()->paginate(10);
    }

    public function show($id): SalesPipeline
    {
        return SalesPipeline::with('prospect.customer')->findOrFail($id);
    }

    public function create(array $data): SalesPipeline
    {
        return SalesPipeline::create($data);
    }

    public function delete($id): bool
    {
        return SalesPipeline::findOrFail($id)->delete();
    }
}