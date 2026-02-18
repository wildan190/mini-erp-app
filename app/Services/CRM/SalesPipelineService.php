<?php

namespace App\Services\CRM;

use App\Models\CRM\SalesPipeline;
use Illuminate\Support\Str;

class SalesPipelineService
{
    public function index()
    {
        return SalesPipeline::with('prospect.customer')->latest()->paginate(10);
    }

    public function show($id): SalesPipeline
    {
        if (is_numeric($id)) {
            return SalesPipeline::with('prospect.customer')->findOrFail($id);
        }
        if (Str::isUuid($id)) {
            return SalesPipeline::with('prospect.customer')->where('uuid', $id)->firstOrFail();
        }
        abort(404);
    }

    public function create(array $data): SalesPipeline
    {
        if (isset($data['prospect_id'])) {
            if (Str::isUuid($data['prospect_id'])) {
                $data['prospect_id'] = \App\Models\CRM\Prospect::where('uuid', $data['prospect_id'])->value('id');
            }
        }

        $data['user_id'] = auth()->id();

        return SalesPipeline::create($data);
    }

    public function delete($id): bool
    {
        if (is_numeric($id)) {
            $pipeline = SalesPipeline::findOrFail($id);
        } elseif (Str::isUuid($id)) {
            $pipeline = SalesPipeline::where('uuid', $id)->firstOrFail();
        } else {
            abort(404);
        }
        return $pipeline->delete();
    }
}