<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;

use App\Http\Controllers\Controller;
use App\Services\CRM\SalesPipelineService;
use Illuminate\Http\Request;

class SalesPipeLineController extends Controller
{
    public function index(SalesPipelineService $service)
    {
        return response()->json([
            'message' => 'List sales pipeline',
            'data' => $service->index()
        ]);
    }

    public function show($id, SalesPipelineService $service)
    {
        return response()->json([
            'message' => 'Detail sales pipeline',
            'data' => $service->show($id)
        ]);
    }

    public function store(Request $request, SalesPipelineService $service)
    {
        $data = $request->validate([
            'prospect_id' => 'required|exists:prospects,id',
            'stage' => 'required|string'
        ]);

        $pipeline = $service->create($data);

        return response()->json([
            'message' => 'Sales pipeline berhasil disimpan',
            'data' => $pipeline
        ], 201);
    }

    public function destroy($id, SalesPipelineService $service)
    {
        $service->delete($id);

        return response()->json([
            'message' => 'Sales pipeline berhasil dihapus'
        ]);
    }
}