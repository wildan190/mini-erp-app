<?php

namespace App\Domain\System\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\System\Models\CalendarEvent;
use App\Domain\System\Models\CalendarTask;
use App\Domain\HRM\Models\Interview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    // ==========================================
    // 1. GET CALENDAR OVERVIEW (Events + Interviews + Tasks)
    // ==========================================
    public function index(Request $request): JsonResponse
    {
        $start = $request->query('start_date') ? Carbon::parse($request->query('start_date'))->startOfDay() : Carbon::now()->startOfMonth()->subDays(7);
        $end = $request->query('end_date') ? Carbon::parse($request->query('end_date'))->endOfDay() : Carbon::now()->endOfMonth()->addDays(7);
        $user = auth()->user();

        // Events
        $events = CalendarEvent::with('creator:id,uuid,name,email')
            ->whereBetween('start_time', [$start, $end])
            ->orderBy('start_time', 'asc')
            ->get();

        // Tasks
        $tasks = CalendarTask::with(['creator:id,uuid,name,email', 'assignee:id,uuid,name,email'])
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('due_date', 'asc')
            ->get();

        // Check if user has global HR / Admin rights
        $isHrOrAdmin = false;
        if ($user) {
            $roles = method_exists($user, 'roles') ? $user->roles->pluck('slug')->toArray() : [];
            $isHrOrAdmin = in_array('super-admin', $roles) ||
                           in_array('admin', $roles) ||
                           in_array('director', $roles) ||
                           in_array('hr-manager', $roles) ||
                           in_array('recruiter', $roles);
        }

        // Automatic Integration: HR Interviews synced into calendar
        $interviewQuery = Interview::with('applicant.jobPost')
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at', 'asc');

        // If user is not HR/Admin, only show interviews where they are assigned as interviewer
        if (!$isHrOrAdmin && $user) {
            $userEmail = strtolower(trim($user->email ?? ''));
            $userName = strtolower(trim($user->name ?? ''));

            $interviewQuery->where(function ($q) use ($userEmail, $userName) {
                if (!empty($userEmail)) {
                    $q->whereRaw('LOWER(interviewer_email) LIKE ?', ["%{$userEmail}%"]);
                }
                if (!empty($userName)) {
                    $q->orWhereRaw('LOWER(interviewer_name) LIKE ?', ["%{$userName}%"]);
                }
            });
        }

        $interviews = $interviewQuery->get()
            ->map(function ($int) {
                return [
                    'id' => 'int-' . $int->id,
                    'uuid' => $int->uuid,
                    'title' => 'Interview: ' . $int->title . ' (' . ($int->applicant?->full_name ?? 'Candidate') . ')',
                    'description' => "Interviewer: {$int->interviewer_name}\nCandidate: {$int->applicant?->full_name}\nLocation/Link: {$int->meeting_link_or_location}",
                    'start_time' => $int->scheduled_at,
                    'end_time' => $int->scheduled_at ? Carbon::parse($int->scheduled_at)->addMinutes(60) : null,
                    'all_day' => false,
                    'location' => $int->meeting_link_or_location,
                    'category' => 'interview',
                    'color' => '#8B5CF6', // Purple for recruitment
                    'status' => $int->status,
                    'attendees' => $int->interviewer_name,
                    'is_synced_interview' => true,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events,
                'tasks' => $tasks,
                'interviews' => $interviews,
            ]
        ]);
    }

    // ==========================================
    // 2. EVENT CRUD
    // ==========================================
    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|in:meeting,interview,milestone,holiday,general',
            'color' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:scheduled,ongoing,completed,cancelled',
            'attendees' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = auth()->id();
        $event = CalendarEvent::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Calendar event created successfully',
            'data' => $event->load('creator:id,uuid,name,email')
        ], 201);
    }

    public function updateEvent(Request $request, string $uuid): JsonResponse
    {
        $event = CalendarEvent::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|in:meeting,interview,milestone,holiday,general',
            'color' => 'nullable|string|max:50',
            'status' => 'required|string|in:scheduled,ongoing,completed,cancelled',
            'attendees' => 'nullable|string|max:500',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Calendar event updated successfully',
            'data' => $event->load('creator:id,uuid,name,email')
        ]);
    }

    public function destroyEvent(string $uuid): JsonResponse
    {
        $event = CalendarEvent::where('uuid', $uuid)->firstOrFail();
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Calendar event deleted successfully'
        ]);
    }

    // ==========================================
    // 3. TASK CRUD
    // ==========================================
    public function storeTask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'due_time' => 'nullable',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'status' => 'nullable|string|in:pending,in_progress,completed,cancelled',
            'category' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = auth()->id();
        $task = CalendarTask::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->load(['creator:id,uuid,name,email', 'assignee:id,uuid,name,email'])
        ], 201);
    }

    public function updateTask(Request $request, string $uuid): JsonResponse
    {
        $task = CalendarTask::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'due_time' => 'nullable',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'category' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task->load(['creator:id,uuid,name,email', 'assignee:id,uuid,name,email'])
        ]);
    }

    public function destroyTask(string $uuid): JsonResponse
    {
        $task = CalendarTask::where('uuid', $uuid)->firstOrFail();
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }
}
