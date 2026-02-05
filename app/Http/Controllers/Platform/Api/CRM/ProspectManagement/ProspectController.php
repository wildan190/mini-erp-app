<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\ProspectStatusRequest;
use App\Models\Prospect;
use App\Services\CRM\ProspectService;
use Illuminate\Http\Request;


class ProspectController extends Controller
{
    public function store(Request $request, ProspectService $service)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|string'
        ]);


        $prospect = $service->create($data);


        return response()->json([
            'message' => 'Prospect berhasil dibuat',
            'data' => $prospect
        ], 201);
    }


    public function updateStatus(ProspectStatusRequest $request, Prospect $prospect, ProspectService $service)
    {
        $service->updateStatus($prospect, $request->status);


        return response()->json([
            'message' => 'Status prospect berhasil diperbarui',
            'data' => $prospect
        ]);
    }
}