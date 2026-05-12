<?php

namespace App\Http\Controllers\Api\Platform\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\ProjectMember;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_uuid' => 'required|uuid|exists:projects,uuid',
            'employee_uuid' => 'required|uuid|exists:employees,uuid',
            'role' => 'required|string|max:255',
            'allocation_percentage' => 'required|integer|min:1|max:100',
        ]);

        $member = ProjectMember::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $member
        ], 201);
    }
}
