<?php

namespace App\Http\Controllers\Platform\Api\CRM\ProspectManagement;


use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CRM\ProspectManagement\ProspectStatusRequest;
use App\Models\Prospect;

class ProspectStatusController extends Controller
{
    public function update(ProspectStatusRequest $request, $id)
    {
        $prospect = Prospect::findOrFail($id);
        $prospect->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status prospect berhasil diperbarui',
            'data' => $prospect
        ]);
    }
}