<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\SalesPipeLineRequest;
use App\Models\SalesPipeline;


class SalesPipeLineController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Data sales pipeline'
        ]);
    }


    public function store(SalesPipeLineRequest $request)
    {
        $pipeline = SalesPipeline::create($request->validated());

        return response()->json([
            'message' => 'Sales pipeline berhasil dibuat',
            'data' => $pipeline
        ], 201);
    }
}