<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\LeadTrackingRequest;
use App\Services\CRM\LeadService;


class LeadTrackingController extends Controller
{
    public function store(LeadTrackingRequest $request, LeadService $service)
    {
        $lead = $service->create($request->validated());


        return response()->json([
            'message' => 'Lead berhasil disimpan',
            'data' => $lead
        ], 201);
    }
}