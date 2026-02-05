<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\LeadTrackingRequest;
use App\Models\Lead;


class LeadTrackingController extends Controller
{
    public function store(LeadTrackingRequest $request)
    {
        $lead = Lead::create($request->validated());

        return response()->json([
            'message' => 'Lead berhasil disimpan',
            'data' => $lead
        ], 201);
    }
}