<?php

namespace App\Services\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\Payroll;
use App\Models\HRM\Resignation;
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

        $costsByMonth = Payroll::whereYear('created_at', $year)
            ->select(
                DB::raw('strftime("%m", created_at) as month'),
                DB::raw('SUM(net_salary) as total_net_salary')
            )
            ->groupBy('month')
            ->orderBy('month')
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
}
