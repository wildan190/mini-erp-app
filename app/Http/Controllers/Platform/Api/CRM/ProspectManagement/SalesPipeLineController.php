<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\SalesPipeLineRequest;
use App\Services\CRM\SalesPipelineService;


class SalesPipeLineController extends Controller
{
    public function index(SalesPipelineService $service)
    {
        $pipelines = $service->index();

        return response()->json([
            'message' => 'List sales pipeline',
            'data' => $pipelines
        ]);
    }

    public function store(SalesPipeLineRequest $request, SalesPipelineService $service)
    {
        $pipeline = $service->create($request->validated());


        return response()->json([
            'message' => 'Sales pipeline berhasil dibuat',
            'data' => $pipeline
        ], 201);
    }
}