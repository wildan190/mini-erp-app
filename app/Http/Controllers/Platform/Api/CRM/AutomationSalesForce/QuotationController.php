<?php

namespace App\Http\Controllers\Platform\Api\CRM\AutomationSalesForce;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\AutomationSalesForce\QuotationRequest;
use App\Models\Quotation;


class QuotationController extends Controller
{
    public function store(QuotationRequest $request)
    {
        $quotation = Quotation::create($request->validated());

        return response()->json([
            'message' => 'Quotation berhasil dibuat',
            'data' => $quotation
        ], 201);
    }
}