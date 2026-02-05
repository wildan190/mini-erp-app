<?php

namespace App\Http\Controllers\Platform\Api\CRM\AutomationSalesForce;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\AutomationSalesForce\QuotationRequest;
use App\Services\CRM\QuotationService;


class QuotationController extends Controller
{
    public function store(QuotationRequest $request, QuotationService $service)
    {
        $quotation = $service->create($request->validated());


        return response()->json([
            'message' => 'Quotation berhasil dibuat',
            'data' => $quotation
        ], 201);
    }
}