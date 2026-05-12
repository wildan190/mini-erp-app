<?php

namespace App\Http\Controllers\Api\Platform\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\ProjectCost;
use Illuminate\Http\Request;

class ProjectCostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_uuid' => 'required|uuid|exists:projects,uuid',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $cost = ProjectCost::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $cost
        ], 201);
    }
}
