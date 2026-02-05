<?php

namespace App\Http\Controllers\Platform\Api\CRM\Dashboard;


use App\Http\Controllers\Controller;
use App\Models\{Customer, Lead, Prospect, SalesPipeline, Quotation};


class CrmDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'metrics' => [
                'total_customers' => Customer::count(),
                'total_leads' => Lead::count(),
                'total_prospects' => Prospect::count(),
                'active_pipelines' => SalesPipeline::count(),
                'total_quotation' => Quotation::count(),
                'quotation_value' => Quotation::sum('amount'),
                'prospect_by_status' => Prospect::selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get(),
            ]
        ]);
    }
}