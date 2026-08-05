<?php

namespace App\Domain\Project\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectCost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectCostController extends Controller
{
    /**
     * Add a cost entry to a project (standalone route, not nested).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_uuid'   => 'required|string|exists:projects,uuid',
            'type'           => 'required|in:labor,material,operational,other',
            'description'    => 'required|string',
            'amount'         => 'required|numeric|min:0',
            'date'           => 'required|date',
            'reference_uuid' => 'nullable|string',
        ]);

        $project = Project::where('uuid', $validated['project_uuid'])->firstOrFail();
        $cost    = $project->costs()->create($validated);

        return response()->json(['message' => 'Cost entry added successfully', 'data' => $cost], 201);
    }

    /**
     * Delete a cost entry.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $cost = ProjectCost::where('uuid', $uuid)->firstOrFail();
        $cost->delete();

        return response()->json(['message' => 'Cost entry deleted']);
    }
}
