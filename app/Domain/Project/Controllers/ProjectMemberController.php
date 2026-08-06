<?php

namespace App\Domain\Project\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    /**
     * List all members across projects (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $members = ProjectMember::with(['project'])
            ->paginate($request->input('per_page', 15));

        return response()->json(['message' => 'List of project members', 'data' => $members]);
    }

    /**
     * Add a member to a project.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_uuid'          => 'required|string|exists:projects,uuid',
            'employee_uuid'         => 'required|string',
            'role'                  => 'nullable|string|max:100',
            'allocation_percentage' => 'nullable|integer|min:1|max:100',
        ]);

        $project = Project::where('uuid', $validated['project_uuid'])->firstOrFail();

        $member = $project->members()->firstOrCreate(
            ['employee_uuid' => $validated['employee_uuid']],
            [
                'role'                  => $validated['role'] ?? 'Member',
                'allocation_percentage' => $validated['allocation_percentage'] ?? 100,
            ]
        );

        return response()->json(['message' => 'Member added to project', 'data' => $member], 201);
    }

    /**
     * Remove a member from a project.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $member = ProjectMember::where('uuid', $uuid)->firstOrFail();
        $member->delete();

        return response()->json(['message' => 'Member removed from project']);
    }
}
