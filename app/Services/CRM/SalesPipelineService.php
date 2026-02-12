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
        return SalesPipeline::with('prospect.customer')->where('uuid', $id)->firstOrFail();
    }

    public function create(array $data): SalesPipeline
    {
        if (isset($data['prospect_id'])) {
            $data['prospect_id'] = \App\Models\Prospect::where('uuid', $data['prospect_id'])->value('id');
        }

        $data['user_id'] = auth()->id();

        return SalesPipeline::create($data);
    }

    public function delete($id): bool
    {
        return SalesPipeline::where('uuid', $id)->firstOrFail()->delete();
    }
}