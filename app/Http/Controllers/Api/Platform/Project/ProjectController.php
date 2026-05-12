<?php

namespace App\Http\Controllers\Api\Platform\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['tasks', 'members'])->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $projects
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'pm_uuid' => 'nullable|uuid',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'value' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $validated['code'] = 'PRJ-' . strtoupper(Str::random(6));

        $project = Project::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $project
        ], 201);
    }

    public function show($uuid)
    {
        $project = Project::with(['tasks.subtasks', 'members', 'timesheets', 'costs'])->findOrFail($uuid);
        return response()->json([
            'status' => 'success',
            'data' => $project
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $project = Project::findOrFail($uuid);
        $project->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $project
        ]);
    }

    public function dashboard()
    {
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'pending_tasks' => \App\Models\Project\ProjectTask::where('status', '!=', 'done')->count(),
            'total_value' => Project::sum('value'),
        ];

        $recent_projects = Project::latest()->take(5)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'recent_projects' => $recent_projects
            ]
        ]);
    }

    public function resources()
    {
        $members = \App\Models\Project\ProjectMember::with(['project', 'employee'])->get();
        $total_employees = \App\Models\HRM\Employee::count();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'members' => $members,
                'stats' => [
                    'total_resources' => $total_employees,
                    'avg_allocation' => \App\Models\Project\ProjectMember::avg('allocation_percentage') ?: 0,
                    'over_allocated' => 0 
                ]
            ]
        ]);
    }

    public function financials()
    {
        $total_budget = Project::sum('value');
        $actual_cost = \App\Models\Project\ProjectCost::sum('amount');
        
        $recent_expenses = \App\Models\Project\ProjectCost::with('project')->latest()->take(10)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => [
                    'total_budget' => $total_budget,
                    'actual_cost' => $actual_cost,
                    'remaining' => $total_budget - $actual_cost,
                    'margin_percentage' => $total_budget > 0 ? (($total_budget - $actual_cost) / $total_budget) * 100 : 0
                ],
                'recent_expenses' => $recent_expenses,
                'project_spending' => Project::withCount(['costs as total_spent' => function($query) {
                    $query->select(\DB::raw('sum(amount)'));
                }])->get()
            ]
        ]);
    }
}
