<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|string'
        ]);

        $prospect = Prospect::create($data);

        return response()->json([
            'message' => 'Prospect berhasil dibuat',
            'data' => $prospect
        ], 201);
    }
}
