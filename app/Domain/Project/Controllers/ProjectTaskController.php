<?php

namespace App\Domain\Project\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    /**
     * List tasks, optionally filtered by project_uuid.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProjectTask::query();

        if ($request->filled('project_uuid')) {
            $query->where('project_uuid', $request->project_uuid);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->with(['project'])->orderBy('order')->paginate($request->input('per_page', 20));

        return response()->json(['message' => 'List of tasks', 'data' => $tasks]);
    }

    /**
     * Create a standalone task (not nested under a project route).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_uuid'            => 'required|string|exists:projects,uuid',
            'name'                    => 'required|string|max:255',
            'description'             => 'nullable|string',
            'assigned_employee_uuid'  => 'nullable|string',
            'parent_task_uuid'        => 'nullable|string|exists:project_tasks,uuid',
            'start_date'              => 'nullable|date',
            'due_date'                => 'nullable|date',
            'status'                  => 'nullable|in:todo,in_progress,review,done',
            'is_milestone'            => 'nullable|boolean',
        ]);

        $project = Project::where('uuid', $validated['project_uuid'])->firstOrFail();
        $task    = $project->tasks()->create(array_merge($validated, [
            'status' => $validated['status'] ?? 'todo',
        ]));

        return response()->json(['message' => 'Task created successfully', 'data' => $task], 201);
    }

    /**
     * Update task status, progress, or other fields.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $task      = ProjectTask::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'name'                => 'sometimes|required|string|max:255',
            'description'         => 'nullable|string',
            'status'              => 'nullable|in:todo,in_progress,review,done',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'due_date'            => 'nullable|date',
            'order'               => 'nullable|integer',
        ]);

        $task->update($validated);

        return response()->json(['message' => 'Task updated successfully', 'data' => $task]);
    }

    /**
     * Bulk reorder tasks (drag-and-drop support).
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tasks'          => 'required|array',
            'tasks.*.uuid'   => 'required|string|exists:project_tasks,uuid',
            'tasks.*.order'  => 'required|integer',
        ]);

        foreach ($validated['tasks'] as $item) {
            ProjectTask::where('uuid', $item['uuid'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Tasks reordered successfully']);
    }
}
