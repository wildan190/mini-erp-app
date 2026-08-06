<?php

namespace App\Domain\Project\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Project Management", description: "Full project lifecycle management: projects, tasks, timesheets, costs")]
class ProjectController extends Controller
{
    // ─── PROJECTS ─────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/platform/project/projects",
        summary: "List projects with pagination",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["planning", "active", "on_hold", "completed", "cancelled"])),
            new OA\Parameter(name: "priority", in: "query", schema: new OA\Schema(type: "string", enum: ["low", "medium", "high"])),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [new OA\Response(response: 200, description: "List of projects")]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Project::withCount(['tasks', 'members'])
            ->with(['tasks' => fn($q) => $q->limit(5)])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json(['message' => 'List of projects', 'data' => $query->paginate($request->input('per_page', 15))]);
    }

    #[OA\Post(
        path: "/api/platform/project/projects",
        summary: "Create new project",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "ERP Upgrade Q4"),
                    new OA\Property(property: "code", type: "string", example: "PROJ-001"),
                    new OA\Property(property: "client_name", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "start_date", type: "string", format: "date"),
                    new OA\Property(property: "end_date", type: "string", format: "date"),
                    new OA\Property(property: "status", type: "string", enum: ["planning", "active", "on_hold", "completed", "cancelled"]),
                    new OA\Property(property: "priority", type: "string", enum: ["low", "medium", "high"]),
                    new OA\Property(property: "value", type: "number"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Project created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'value' => 'nullable|numeric|min:0',
        ]);

        $validated['status'] = $validated['status'] ?? 'planning';
        $validated['priority'] = $validated['priority'] ?? 'medium';
        if (empty($validated['code'])) {
            $validated['code'] = 'PRJ-' . strtoupper(\Illuminate\Support\Str::random(6));
        }

        $project = Project::create($validated);
        return response()->json(['message' => 'Project created successfully', 'data' => $project], 201);
    }

    #[OA\Get(
        path: "/api/platform/project/projects/{uuid}",
        summary: "Get project detail",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 200, description: "Project detail")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $project = Project::withCount(['tasks', 'members'])
            ->with(['tasks', 'members', 'costs'])
            ->where('uuid', $uuid)->firstOrFail();

        return response()->json(['message' => 'Project detail', 'data' => $project]);
    }

    #[OA\Put(
        path: "/api/platform/project/projects/{uuid}",
        summary: "Update project",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "status", type: "string", enum: ["planning", "active", "on_hold", "completed", "cancelled"]),
                    new OA\Property(property: "priority", type: "string"),
                    new OA\Property(property: "end_date", type: "string", format: "date"),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Project updated")]
    )]
    public function update(Request $request, string $uuid): JsonResponse
    {
        $project = Project::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'value' => 'nullable|numeric|min:0',
        ]);
        $project->update($validated);
        return response()->json(['message' => 'Project updated successfully', 'data' => $project]);
    }

    // ─── TASKS ────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/platform/project/projects/{uuid}/tasks",
        summary: "List tasks of a project",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [new OA\Response(response: 200, description: "List of tasks")]
    )]
    public function tasks(Request $request, string $uuid): JsonResponse
    {
        $project = Project::where('uuid', $uuid)->firstOrFail();
        $query = $project->tasks()->with('subtasks')->whereNull('parent_task_uuid');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        return response()->json(['message' => 'List of tasks', 'data' => $query->orderBy('order')->get()]);
    }

    #[OA\Post(
        path: "/api/platform/project/projects/{uuid}/tasks",
        summary: "Add task to project",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "assigned_employee_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "parent_task_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "start_date", type: "string", format: "date"),
                    new OA\Property(property: "due_date", type: "string", format: "date"),
                    new OA\Property(property: "is_milestone", type: "boolean"),
                    new OA\Property(property: "status", type: "string", enum: ["todo", "in_progress", "review", "done"]),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Task created")]
    )]
    public function storeTask(Request $request, string $uuid): JsonResponse
    {
        $project = Project::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_employee_uuid' => 'nullable|string',
            'parent_task_uuid' => 'nullable|string|exists:project_tasks,uuid',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'is_milestone' => 'nullable|boolean',
            'status' => 'nullable|in:todo,in_progress,review,done',
        ]);

        $task = $project->tasks()->create(array_merge($validated, [
            'status' => $validated['status'] ?? 'todo',
        ]));

        return response()->json(['message' => 'Task created successfully', 'data' => $task], 201);
    }

    #[OA\Patch(
        path: "/api/platform/project/tasks/{task_uuid}",
        summary: "Update task status or progress",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "task_uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["todo", "in_progress", "review", "done"]),
                    new OA\Property(property: "progress_percentage", type: "integer", minimum: 0, maximum: 100),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Task updated")]
    )]
    public function updateTask(Request $request, string $taskUuid): JsonResponse
    {
        $task = ProjectTask::where('uuid', $taskUuid)->firstOrFail();
        $validated = $request->validate([
            'status' => 'nullable|in:todo,in_progress,review,done',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
        $task->update($validated);
        return response()->json(['message' => 'Task updated successfully', 'data' => $task]);
    }

    // ─── MEMBERS ──────────────────────────────────────────────────────────────

    #[OA\Post(
        path: "/api/platform/project/projects/{uuid}/members",
        summary: "Add member to project",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["employee_uuid"],
                properties: [
                    new OA\Property(property: "employee_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "role", type: "string", example: "Developer"),
                    new OA\Property(property: "allocation_percentage", type: "integer", minimum: 1, maximum: 100),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Member added")]
    )]
    public function storeMember(Request $request, string $uuid): JsonResponse
    {
        $project = Project::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'employee_uuid' => 'required|string',
            'role' => 'nullable|string|max:100',
            'allocation_percentage' => 'nullable|integer|min:1|max:100',
        ]);

        $member = $project->members()->firstOrCreate(
            ['employee_uuid' => $validated['employee_uuid']],
            ['role' => $validated['role'] ?? 'Member', 'allocation_percentage' => $validated['allocation_percentage'] ?? 100]
        );

        return response()->json(['message' => 'Member added to project', 'data' => $member], 201);
    }

    // ─── TIMESHEETS ───────────────────────────────────────────────────────────

    #[OA\Post(
        path: "/api/platform/project/projects/{uuid}/timesheets",
        summary: "Log timesheet for a project",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["employee_uuid", "date", "hours"],
                properties: [
                    new OA\Property(property: "employee_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "task_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "date", type: "string", format: "date"),
                    new OA\Property(property: "hours", type: "number"),
                    new OA\Property(property: "notes", type: "string"),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Timesheet logged")]
    )]
    public function storeTimesheet(Request $request, string $uuid): JsonResponse
    {
        $project = Project::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'employee_uuid' => 'required|string',
            'task_uuid' => 'nullable|string|exists:project_tasks,uuid',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'notes' => 'nullable|string',
        ]);

        $timesheet = $project->timesheets()->create(array_merge($validated, ['status' => 'pending']));
        return response()->json(['message' => 'Timesheet logged successfully', 'data' => $timesheet], 201);
    }

    // ─── COSTS ────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/platform/project/projects/{uuid}/costs",
        summary: "List project costs",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 200, description: "List of project costs")]
    )]
    public function costs(string $uuid): JsonResponse
    {
        $project = Project::where('uuid', $uuid)->firstOrFail();
        $costs = $project->costs()->orderBy('date', 'desc')->get();
        $total = $costs->sum('amount');
        return response()->json([
            'message' => 'Project costs',
            'data' => ['total' => $total, 'items' => $costs]
        ]);
    }

    #[OA\Post(
        path: "/api/platform/project/projects/{uuid}/costs",
        summary: "Add cost entry to project",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "description", "amount", "date"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["labor", "material", "operational", "other"]),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "amount", type: "number"),
                    new OA\Property(property: "date", type: "string", format: "date"),
                    new OA\Property(property: "reference_uuid", type: "string"),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Cost entry added")]
    )]
    public function storeCost(Request $request, string $uuid): JsonResponse
    {
        $project = Project::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'type' => 'required|in:labor,material,operational,other',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'reference_uuid' => 'nullable|string',
        ]);

        $cost = $project->costs()->create($validated);
        return response()->json(['message' => 'Cost entry added successfully', 'data' => $cost], 201);
    }

    // ─── DASHBOARD ────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/platform/project/dashboard",
        summary: "Project management dashboard KPIs",
        security: [["sanctum" => []]],
        tags: ["Project Management"],
        responses: [new OA\Response(response: 200, description: "Dashboard stats")]
    )]
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'overdue_tasks' => ProjectTask::where('status', '!=', 'done')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->toDateString())
                ->count(),
        ];

        $active_projects = Project::where('status', 'active')
            ->withCount('tasks')
            ->orderBy('end_date')
            ->limit(5)
            ->get();

        return response()->json([
            'message' => 'Project management dashboard',
            'data' => compact('stats', 'active_projects'),
        ]);
    }
}
