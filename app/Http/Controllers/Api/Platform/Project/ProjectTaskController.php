<?php

namespace App\Http\Controllers\Api\Platform\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\ProjectTask;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ProjectTask::query();
            
            if ($request->has('project_uuid')) {
                $query->where('project_uuid', $request->project_uuid);
            }

            // Temporarily simplify relationships to debug
            $tasks = $query->with(['project'])->orderBy('order', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_uuid' => 'required|uuid|exists:projects,uuid',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_employee_uuid' => 'nullable|uuid',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string',
            'is_milestone' => 'nullable|boolean',
        ]);

        $task = ProjectTask::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $task
        ], 201);
    }

    public function update(Request $request, $uuid)
    {
        $task = ProjectTask::findOrFail($uuid);
        $task->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $task
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.uuid' => 'required|uuid|exists:project_tasks,uuid',
            'tasks.*.order' => 'required|integer',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            ProjectTask::where('uuid', $taskData['uuid'])->update(['order' => $taskData['order']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tasks reordered successfully'
        ]);
    }
}
