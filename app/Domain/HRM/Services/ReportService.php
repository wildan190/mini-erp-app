<?php

namespace App\Domain\HRM\Services;

use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\Payroll;
use App\Domain\HRM\Models\Resignation;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get employee turnover statistics.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTurnoverStats(?string $startDate, ?string $endDate): array
    {
        $startDate = $startDate ?? now()->startOfYear()->toDateString();
        $endDate = $endDate ?? now()->endOfYear()->toDateString();

        $joined = Employee::whereBetween('joining_date', [$startDate, $endDate])->count();

        $resigned = Resignation::where('status', 'approved')
            ->whereBetween('resignation_date', [$startDate, $endDate])
            ->count();

        $terminated = Employee::where('status', 'terminated')
            ->whereBetween('updated_at', [$startDate, $endDate]) // Assuming updated_at tracks termination time roughly
            ->count();

        $leavers = $resigned + $terminated;

        $totalEmployeesStart = Employee::where('joining_date', '<', $startDate)
            ->where(function ($query) use ($startDate) {
                $query->where('status', 'active')
                    ->orWhere(function ($q) use ($startDate) {
                        $q->whereIn('status', ['resigned', 'terminated'])
                            ->where('updated_at', '>=', $startDate);
                    });
            })->count();

        // Simplified turnover rate: Leavers / ((Start + End) / 2) * 100
        // For now, let's just return raw numbers

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'joined' => $joined,
            'resigned' => $resigned,
            'terminated' => $terminated,
            'total_leavers' => $leavers,
            'net_change' => $joined - $leavers,
        ];
    }

    /**
     * Get labor cost statistics (Payroll).
     *
     * @param string|null $year
     * @return array
     */
    public function getLaborCostStats(?string $year): array
    {
        $year = $year ?? now()->year;

        $driver = DB::connection()->getDriverName();
        $monthFormat = $driver === 'pgsql' ? "to_char(created_at, 'MM')" : "strftime('%m', created_at)";

        $costsByMonth = Payroll::whereYear('created_at', $year)
            ->select(
                DB::raw("$monthFormat as month"),
                DB::raw('SUM(net_salary) as total_net_salary')
            )
            ->groupBy(DB::raw($monthFormat))
            ->orderBy(DB::raw($monthFormat))
            ->get();

        $costsByDepartment = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->whereYear('payrolls.created_at', $year)
            ->select(
                'departments.name as department_name',
                DB::raw('SUM(payrolls.net_salary) as total_cost')
            )
            ->groupBy('department_name')
            ->get();

        return [
            'year' => $year,
            'monthly_costs' => $costsByMonth,
            'department_costs' => $costsByDepartment,
            'total_year_cost' => $costsByMonth->sum('total_net_salary'),
        ];
    }

    /**
     * Calculate individual Employee KPIs and Performance Metrics
     * Based on: Attendance Discipline, Project Task Completion, Resource Allocation, and Leave Balance.
     */
    public function getEmployeeKpiStats(?string $year): array
    {
        $year = $year ? (int)$year : now()->year;

        $employees = Employee::with([
            'department:id,name',
            'designation:id,title',
            'attendances' => function ($q) use ($year) {
                $q->whereYear('date', $year);
            },
            'leaveRequests' => function ($q) use ($year) {
                $q->whereYear('start_date', $year)->where('status', 'approved');
            }
        ])
        ->where('status', 'active')
        ->get();

        // Cross-domain tasks by employee UUID
        $tasksByEmployee = \App\Domain\Project\Models\ProjectTask::whereYear('created_at', $year)
            ->get()
            ->groupBy('assigned_employee_uuid');

        // Cross-domain allocations by employee UUID
        $allocationsByEmployee = \App\Domain\Project\Models\ProjectMember::get()
            ->groupBy('employee_uuid');

        $kpiList = $employees->map(function ($emp) use ($tasksByEmployee, $allocationsByEmployee) {
            $totalAttendances = $emp->attendances->count();
            $presentCount = $emp->attendances->where('status', 'present')->count();
            $lateCount = $emp->attendances->where('status', 'late')->count();
            $absentCount = $emp->attendances->where('status', 'absent')->count();

            // 1. Attendance Discipline Score (Max 100)
            // Present = 100%, Late = 70%, Absent = 0%
            $attendanceRate = $totalAttendances > 0 ? round(($presentCount + ($lateCount * 0.7)) / $totalAttendances * 100, 1) : 100;

            // 2. Project Task Performance Score (Max 100)
            $empTasks = $tasksByEmployee->get($emp->uuid, collect());
            $totalTasks = $empTasks->count();
            $completedTasks = $empTasks->where('status', 'done')->count();
            $inProgressTasks = $empTasks->where('status', 'in_progress')->count();
            $avgTaskProgress = $totalTasks > 0 ? round($empTasks->avg('progress_percentage') ?? 0, 1) : null;
            $taskCompletionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 100;

            // 3. Project Allocation / Workload
            $empAllocations = $allocationsByEmployee->get($emp->uuid, collect());
            $projectCount = $empAllocations->count();
            $totalAllocationPct = $empAllocations->sum('allocation_percentage');

            // 4. Leave Days Taken
            $approvedLeaveDays = $emp->leaveRequests->count(); // total approved requests

            // 5. Overall Performance Score (Weighted Composite Score 0 - 100)
            // Formula: Attendance 40% + Task Completion 40% + Workload factor 20%
            $workloadFactor = min(100, $totalAllocationPct > 0 ? $totalAllocationPct : 80);
            $taskFactor = $totalTasks > 0 ? $taskCompletionRate : 85;
            $overallScore = round(($attendanceRate * 0.40) + ($taskFactor * 0.40) + ($workloadFactor * 0.20), 1);

            // Grade / Badge
            $grade = 'A';
            $rating = 'Outstanding';
            if ($overallScore >= 90) {
                $grade = 'A+';
                $rating = 'Exceptional';
            } elseif ($overallScore >= 80) {
                $grade = 'A';
                $rating = 'Exceeds Expectations';
            } elseif ($overallScore >= 70) {
                $grade = 'B';
                $rating = 'Meets Expectations';
            } elseif ($overallScore >= 60) {
                $grade = 'C';
                $rating = 'Needs Improvement';
            } else {
                $grade = 'D';
                $rating = 'Underperforming';
            }

            return [
                'employee_uuid'       => $emp->uuid,
                'emp_code'            => $emp->emp_code,
                'full_name'           => trim("{$emp->first_name} {$emp->last_name}"),
                'department'          => $emp->department?->name ?? 'General',
                'designation'         => $emp->designation?->title ?? 'Staff',
                'attendance_stats'    => [
                    'total'           => $totalAttendances,
                    'present'         => $presentCount,
                    'late'            => $lateCount,
                    'absent'          => $absentCount,
                    'score'           => $attendanceRate,
                ],
                'task_stats'          => [
                    'total_assigned'  => $totalTasks,
                    'completed'       => $completedTasks,
                    'in_progress'     => $inProgressTasks,
                    'avg_progress'    => $avgTaskProgress,
                    'completion_rate' => $taskCompletionRate,
                ],
                'workload_stats'      => [
                    'active_projects' => $projectCount,
                    'allocation_pct'  => $totalAllocationPct,
                ],
                'leaves_taken'        => $approvedLeaveDays,
                'overall_score'       => $overallScore,
                'grade'               => $grade,
                'rating'              => $rating,
            ];
        });

        // Summary Analytics
        $avgScore = $kpiList->count() > 0 ? round($kpiList->avg('overall_score'), 1) : 0;
        $topPerformers = $kpiList->sortByDesc('overall_score')->take(3)->values();
        $needsAttention = $kpiList->where('overall_score', '<', 70)->values();

        return [
            'year'              => $year,
            'total_evaluated'   => $kpiList->count(),
            'average_kpi_score' => $avgScore,
            'top_performers'    => $topPerformers,
            'needs_attention'   => $needsAttention,
            'kpis'              => $kpiList->sortByDesc('overall_score')->values(),
        ];
    }
}
