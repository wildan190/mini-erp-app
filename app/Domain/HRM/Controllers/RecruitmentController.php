<?php

namespace App\Domain\HRM\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\HRM\Models\JobPost;
use App\Domain\HRM\Models\JobApplicant;
use App\Domain\HRM\Models\Interview;
use App\Domain\HRM\Models\InterviewEvaluation;
use App\Domain\HRM\Models\OfferingLetter;
use App\Domain\HRM\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RecruitmentController extends Controller
{
    // ==========================================
    // 1. JOB POST MANAGEMENT
    // ==========================================
    public function indexJobPosts(Request $request): JsonResponse
    {
        $query = JobPost::with(['department', 'designation'])
            ->withCount('applicants');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $jobs = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    public function storeJobPost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'employment_type' => 'required|string|in:full-time,part-time,contract,internship',
            'location' => 'nullable|string|max:255',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'status' => 'required|string|in:draft,published,closed',
            'deadline_date' => 'nullable|date',
        ]);

        $job = JobPost::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job post created successfully',
            'data' => $job->load(['department', 'designation'])
        ], 201);
    }

    public function updateJobPost(Request $request, string $uuid): JsonResponse
    {
        $job = JobPost::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'employment_type' => 'required|string|in:full-time,part-time,contract,internship',
            'location' => 'nullable|string|max:255',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'status' => 'required|string|in:draft,published,closed',
            'deadline_date' => 'nullable|date',
        ]);

        $job->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job post updated successfully',
            'data' => $job->load(['department', 'designation'])
        ]);
    }

    public function destroyJobPost(string $uuid): JsonResponse
    {
        $job = JobPost::where('uuid', $uuid)->firstOrFail();
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job post deleted successfully'
        ]);
    }

    // ==========================================
    // 2. APPLICANT TRACKING SYSTEM (ATS)
    // ==========================================
    public function indexApplicants(Request $request): JsonResponse
    {
        $query = JobApplicant::with(['jobPost.department', 'jobPost.designation', 'interviews.evaluations', 'latestOfferingLetter', 'convertedEmployee']);

        if ($request->has('job_post_id') && !empty($request->job_post_id)) {
            $query->where('job_post_id', $request->job_post_id);
        }

        if ($request->has('stage') && $request->stage !== 'all') {
            $query->where('stage', $request->stage);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $applicants = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $applicants
        ]);
    }

    public function storeApplicant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_post_id' => 'required|exists:job_posts,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|string|in:male,female',
            'address' => 'nullable|string',
            'resume_path' => 'nullable|string',
            'portfolio_url' => 'nullable|string|max:500',
            'stage' => 'nullable|string|in:screening,technical_test,interview,offering,hired,rejected',
            'notes' => 'nullable|string',
            'expected_salary' => 'nullable|numeric|min:0',
        ]);

        $applicant = JobApplicant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Applicant registered successfully',
            'data' => $applicant->load('jobPost')
        ], 201);
    }

    public function updateApplicantStage(Request $request, string $uuid): JsonResponse
    {
        $applicant = JobApplicant::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'stage' => 'required|string|in:screening,technical_test,interview,offering,hired,rejected',
            'notes' => 'nullable|string',
        ]);

        $applicant->update([
            'stage' => $validated['stage'],
            'notes' => $validated['notes'] ?? $applicant->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Applicant stage updated',
            'data' => $applicant->load(['jobPost', 'interviews', 'latestOfferingLetter'])
        ]);
    }

    public function destroyApplicant(string $uuid): JsonResponse
    {
        $applicant = JobApplicant::where('uuid', $uuid)->firstOrFail();
        $applicant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Applicant removed successfully'
        ]);
    }

    // ==========================================
    // 3. INTERVIEW SCHEDULING & INTERVIEWER ASSIGNMENT
    // ==========================================
    public function indexInterviewers(): JsonResponse
    {
        // Ambil data resmi dari tabel Employee yang aktif
        $employees = Employee::with(['user', 'department', 'designation'])
            ->where('status', 'active')
            ->get();

        $interviewers = $employees->map(function ($emp) {
            $fullName = trim("{$emp->first_name} {$emp->last_name}");
            $email = $emp->user ? $emp->user->email : null;
            $deptTitle = $emp->department ? $emp->department->name : ($emp->designation ? $emp->designation->title : 'HR');

            return [
                'id' => $emp->id,
                'uuid' => $emp->uuid,
                'employee_code' => $emp->emp_code,
                'name' => "{$fullName} ({$deptTitle})",
                'raw_name' => $fullName,
                'email' => $email,
                'phone' => $emp->phone,
                'department' => $emp->department?->name,
                'designation' => $emp->designation?->title,
            ];
        });

        // Fallback jika belum ada data employee di database, gunakan user active
        if ($interviewers->isEmpty()) {
            $users = User::where('status', 'active')->get();
            $interviewers = $users->map(function ($u) {
                return [
                    'id' => $u->id,
                    'uuid' => $u->uuid,
                    'employee_code' => 'USR-' . $u->id,
                    'name' => "{$u->name} (User)",
                    'raw_name' => $u->name,
                    'email' => $u->email,
                    'phone' => null,
                    'department' => null,
                    'designation' => null,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $interviewers
        ]);
    }

    public function indexInterviews(Request $request): JsonResponse
    {
        $query = Interview::with(['applicant.jobPost', 'evaluations']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $interviews = $query->orderBy('scheduled_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $interviews
        ]);
    }

    public function storeInterview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_applicant_id' => 'required|exists:job_applicants,id',
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'type' => 'required|string|in:online,offline',
            'meeting_link_or_location' => 'nullable|string|max:500',
            'interviewer_name' => 'required|string|max:255',
            'interviewer_email' => 'nullable|string|max:500',
            'instructions' => 'nullable|string',
            'status' => 'nullable|string|in:scheduled,completed,cancelled,rescheduled',
        ]);

        $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);
        $windowStart = $scheduledAt->copy()->subMinutes(45);
        $windowEnd = $scheduledAt->copy()->addMinutes(45);

        // 1. Cek bentrok jadwal untuk setiap interviewer yang di-tag
        $interviewerNames = array_map('trim', explode(',', $validated['interviewer_name']));
        $interviewerEmails = !empty($validated['interviewer_email']) ? array_map('trim', explode(',', $validated['interviewer_email'])) : [];

        foreach ($interviewerNames as $idx => $name) {
            $email = $interviewerEmails[$idx] ?? null;

            $interviewerConflict = Interview::where('status', 'scheduled')
                ->where(function ($q) use ($name, $email) {
                    $q->where('interviewer_name', 'LIKE', "%{$name}%");
                    if (!empty($email)) {
                        $q->orWhere('interviewer_email', 'LIKE', "%{$email}%");
                    }
                })
                ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
                ->first();

            if ($interviewerConflict) {
                return response()->json([
                    'success' => false,
                    'message' => "Interviewer {$name} has a conflicting schedule at " . $interviewerConflict->scheduled_at->format('Y-m-d H:i') . " ({$interviewerConflict->title}). Please choose a different time or interviewer."
                ], 422);
            }
        }

        // 2. Cek bentrok jadwal kandidat yang sama
        $applicantConflict = Interview::where('status', 'scheduled')
            ->where('job_applicant_id', $validated['job_applicant_id'])
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->first();

        if ($applicantConflict) {
            return response()->json([
                'success' => false,
                'message' => "Candidate already has another interview scheduled at " . $applicantConflict->scheduled_at->format('Y-m-d H:i') . ". Please choose a different time."
            ], 422);
        }

        $interview = Interview::create($validated);

        // Otomatis update status stage applicant ke 'interview' jika masih di tahap sebelumnya
        $applicant = JobApplicant::find($validated['job_applicant_id']);
        if ($applicant && in_array($applicant->stage, ['screening', 'technical_test'])) {
            $applicant->update(['stage' => 'interview']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Interview scheduled successfully',
            'data' => $interview->load('applicant.jobPost')
        ], 201);
    }

    public function updateInterview(Request $request, string $uuid): JsonResponse
    {
        $interview = Interview::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'type' => 'required|string|in:online,offline',
            'meeting_link_or_location' => 'nullable|string|max:500',
            'interviewer_name' => 'required|string|max:255',
            'interviewer_email' => 'nullable|string|max:500',
            'instructions' => 'nullable|string',
            'status' => 'required|string|in:scheduled,completed,cancelled,rescheduled',
        ]);

        if ($validated['status'] === 'scheduled') {
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);
            $windowStart = $scheduledAt->copy()->subMinutes(45);
            $windowEnd = $scheduledAt->copy()->addMinutes(45);

            $interviewerConflict = Interview::where('status', 'scheduled')
                ->where('id', '!=', $interview->id)
                ->where(function ($q) use ($validated) {
                    if (!empty($validated['interviewer_email'])) {
                        $q->where('interviewer_email', $validated['interviewer_email'])
                          ->orWhere('interviewer_name', $validated['interviewer_name']);
                    } else {
                        $q->where('interviewer_name', $validated['interviewer_name']);
                    }
                })
                ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
                ->first();

            if ($interviewerConflict) {
                return response()->json([
                    'success' => false,
                    'message' => "Interviewer {$validated['interviewer_name']} has a conflicting schedule at " . $interviewerConflict->scheduled_at->format('Y-m-d H:i') . " ({$interviewerConflict->title})."
                ], 422);
            }
        }

        $interview->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Interview updated successfully',
            'data' => $interview->load('applicant.jobPost')
        ]);
    }

    public function destroyInterview(string $uuid): JsonResponse
    {
        $interview = Interview::where('uuid', $uuid)->firstOrFail();
        $interview->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interview removed successfully'
        ]);
    }

    // ==========================================
    // 4. EVALUATION & FEEDBACK
    // ==========================================
    public function storeEvaluation(Request $request, string $interviewUuid): JsonResponse
    {
        $interview = Interview::where('uuid', $interviewUuid)->firstOrFail();

        $validated = $request->validate([
            'evaluator_name' => 'required|string|max:255',
            'technical_score' => 'required|integer|min:1|max:5',
            'communication_score' => 'required|integer|min:1|max:5',
            'culture_fit_score' => 'required|integer|min:1|max:5',
            'feedback_notes' => 'nullable|string',
            'recommendation' => 'required|string|in:hire,consider,reject',
        ]);

        $overall = round(($validated['technical_score'] + $validated['communication_score'] + $validated['culture_fit_score']) / 3);

        $evaluation = InterviewEvaluation::create([
            'interview_id' => $interview->id,
            'evaluator_name' => $validated['evaluator_name'],
            'technical_score' => $validated['technical_score'],
            'communication_score' => $validated['communication_score'],
            'culture_fit_score' => $validated['culture_fit_score'],
            'overall_score' => $overall,
            'feedback_notes' => $validated['feedback_notes'] ?? null,
            'recommendation' => $validated['recommendation'],
        ]);

        $interview->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation submitted successfully',
            'data' => $evaluation
        ], 201);
    }

    // ==========================================
    // 5. OFFERING LETTER
    // ==========================================
    public function indexOfferingLetters(): JsonResponse
    {
        $offers = OfferingLetter::with(['applicant.jobPost.department', 'applicant.jobPost.designation'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $offers
        ]);
    }

    public function storeOfferingLetter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_applicant_id' => 'required|exists:job_applicants,id',
            'basic_salary' => 'required|numeric|min:0',
            'benefits' => 'nullable|string',
            'joining_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'terms_and_conditions' => 'nullable|string',
            'status' => 'required|string|in:draft,sent,accepted,rejected,expired',
        ]);

        $offerCount = OfferingLetter::count() + 1;
        $offerNumber = 'OFF/' . date('Ym') . '/' . str_pad($offerCount, 4, '0', STR_PAD_LEFT);

        $offer = OfferingLetter::create([
            'job_applicant_id' => $validated['job_applicant_id'],
            'offer_number' => $offerNumber,
            'basic_salary' => $validated['basic_salary'],
            'benefits' => $validated['benefits'] ?? null,
            'joining_date' => $validated['joining_date'],
            'expiry_date' => $validated['expiry_date'] ?? null,
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'status' => $validated['status'],
            'responded_at' => in_array($validated['status'], ['accepted', 'rejected']) ? now() : null,
        ]);

        // Advance applicant stage to 'offering' or 'hired'
        $applicant = JobApplicant::find($validated['job_applicant_id']);
        if ($applicant) {
            if ($validated['status'] === 'accepted') {
                $applicant->update(['stage' => 'hired']);
            } else {
                $applicant->update(['stage' => 'offering']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Offering letter created successfully',
            'data' => $offer->load('applicant.jobPost')
        ], 201);
    }

    public function updateOfferingLetterStatus(Request $request, string $uuid): JsonResponse
    {
        $offer = OfferingLetter::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|string|in:draft,sent,accepted,rejected,expired',
        ]);

        $offer->update([
            'status' => $validated['status'],
            'responded_at' => in_array($validated['status'], ['accepted', 'rejected']) ? now() : null,
        ]);

        if ($validated['status'] === 'accepted' && $offer->applicant) {
            $offer->applicant->update(['stage' => 'hired']);
        } elseif ($validated['status'] === 'rejected' && $offer->applicant) {
            $offer->applicant->update(['stage' => 'rejected']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Offering status updated',
            'data' => $offer->load('applicant.jobPost')
        ]);
    }

    // ==========================================
    // 6. ONBOARDING PREPARATION & CONVERT TO EMPLOYEE (1-CLICK)
    // ==========================================
    public function convertToEmployee(Request $request, string $applicantUuid): JsonResponse
    {
        $applicant = JobApplicant::with(['jobPost', 'latestOfferingLetter'])->where('uuid', $applicantUuid)->firstOrFail();

        if ($applicant->converted_employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'This applicant has already been converted to an employee.'
            ], 422);
        }

        return DB::transaction(function () use ($applicant, $request) {
            $jobPost = $applicant->jobPost;
            $latestOffer = $applicant->latestOfferingLetter;

            // Generate unique employee code
            $empCount = Employee::count() + 1;
            $empCode = 'EMP-' . date('Y') . '-' . str_pad($empCount, 4, '0', STR_PAD_LEFT);

            // Optional Create User Account
            $user = User::create([
                'name' => $applicant->full_name,
                'email' => $applicant->email,
                'password' => Hash::make('password123'), // Default initial password
                'status' => 'active',
            ]);

            // Create Employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'emp_code' => $empCode,
                'first_name' => $applicant->first_name,
                'last_name' => $applicant->last_name,
                'phone' => $applicant->phone,
                'gender' => $applicant->gender,
                'address' => $applicant->address,
                'department_id' => $jobPost ? $jobPost->department_id : null,
                'designation_id' => $jobPost ? $jobPost->designation_id : null,
                'joining_date' => $latestOffer ? $latestOffer->joining_date : now()->format('Y-m-d'),
                'basic_salary' => $latestOffer ? $latestOffer->basic_salary : ($applicant->expected_salary ?? 0),
                'status' => 'active',
            ]);

            // Link applicant to new employee
            $applicant->update([
                'converted_employee_id' => $employee->id,
                'stage' => 'hired',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Applicant successfully onboarded as Employee ({$empCode})! Default user account created.",
                'data' => [
                    'employee' => $employee->load(['department', 'designation']),
                    'applicant' => $applicant
                ]
            ]);
        });
    }
}
