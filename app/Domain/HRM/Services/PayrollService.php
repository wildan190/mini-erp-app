<?php

namespace App\Domain\HRM\Services;

use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\Payroll;
use App\Domain\HRM\Models\PayrollItem;
use App\Domain\HRM\Models\PayrollPeriod;
use App\Domain\HRM\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;


class PayrollService
{
    /**
     * Get payrolls with filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPayrolls(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Payroll::with(['employee.user', 'payrollPeriod'])
            ->when(isset($filters['payroll_period_uuid']), function (Builder $query) use ($filters) {
                $period = PayrollPeriod::where('uuid', $filters['payroll_period_uuid'])->first();
                $query->where('payroll_period_id', $period?->id ?? 0);
            })
            ->when(isset($filters['employee_uuid']), function (Builder $query) use ($filters) {
                $employee = Employee::where('uuid', $filters['employee_uuid'])->first();
                $query->where('employee_id', $employee?->id ?? 0);
            })
            ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Generate payroll for a period.
     *
     * @param PayrollPeriod $period
     * @return int Number of payrolls generated
     * @throws \Exception
     */
    public function generatePayroll(PayrollPeriod $period): int
    {
        if ($period->status === 'closed') {
            throw new \Exception('Payroll period is closed.');
        }

        $count = 0;

        // Use chunk() instead of get() — avoids loading all employees into memory at once.
        // For large orgs this is the difference between 50MB and 500MB peak RAM.
        Employee::where('status', 'active')->chunk(50, function ($employees) use ($period, &$count) {
            foreach ($employees as $employee) {
                if (Payroll::where('employee_id', $employee->id)
                    ->where('payroll_period_id', $period->id)
                    ->exists()
                ) {
                    continue;
                }

                $this->calculateSalary($employee, $period);
                $count++;
            }
        });

        $period->update(['status' => 'processing']);

        return $count;
    }

    /**
     * Calculate salary for an employee.
     *
     * @param Employee $employee
     * @param PayrollPeriod $period
     * @return Payroll
     */
    public function calculateSalary(Employee $employee, PayrollPeriod $period): Payroll
    {
        return DB::transaction(function () use ($employee, $period) {
            $basicSalary = $employee->basic_salary;
            $totalEarnings = $basicSalary;
            $totalDeductions = 0;

            // 1. Calculate Expected Work Days (Monday - Friday)
            $expectedWorkDays = $this->countWorkDays($period->start_date, $period->end_date);

            // 2. Count Actual Presence
            $actualPresence = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->whereIn('status', ['present', 'late'])
                ->count();

            $absenceDays = max(0, $expectedWorkDays - $actualPresence);

            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'payroll_period_id' => $period->id,
                'basic_salary' => $basicSalary,
                'expected_work_days' => $expectedWorkDays,
                'actual_presence' => $actualPresence,
                'absence_days' => $absenceDays,
                'status' => 'draft',
            ]);

            // Add Basic Salary Item
            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'name' => 'Basic Salary',
                'amount' => $basicSalary,
                'type' => 'earning',
            ]);

            // 3. Apply Absence Deduction
            if ($absenceDays > 0 && $expectedWorkDays > 0) {
                $deductionAmount = ($absenceDays / $expectedWorkDays) * $basicSalary;

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'name' => "Absence Deduction ($absenceDays days)",
                    'amount' => $deductionAmount,
                    'type' => 'deduction',
                ]);

                $totalDeductions += $deductionAmount;
            }

            // Use components assigned specifically to this employee.
            // custom_value on the pivot overrides the component's default value.
            $components = $employee->salaryComponents()->where('is_active', true)->get();

            foreach ($components as $component) {
                // Resolve effective value: pivot custom_value takes precedence
                $effectiveValue = $component->pivot->custom_value ?? $component->value;
                $amount = 0;

                if ($component->is_fixed) {
                    $amount = $effectiveValue;
                } elseif ($component->percentage_of === 'basic_salary') {
                    $amount = ($basicSalary * $effectiveValue) / 100;
                }

                if ($amount > 0) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'salary_component_id' => $component->id,
                        'name' => $component->name,
                        'amount' => $amount,
                        'type' => $component->type,
                    ]);

                    if ($component->type === 'earning') {
                        $totalEarnings += $amount;
                    } else {
                        $totalDeductions += $amount;
                    }
                }
            }

            $netSalary = $totalEarnings - $totalDeductions;

            $payroll->update([
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
            ]);

            return $payroll;
        });
    }

    /**
     * Count work days (Monday - Friday) between two dates.
     */
    private function countWorkDays($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workDays = 0;

        while ($start <= $end) {
            if (!$start->isWeekend()) { // isWeekend() checks for Sat & Sun by default
                $workDays++;
            }
            $start->addDay();
        }

        return $workDays;
    }

    /**
     * Process/Pay a payroll.
     *
     * @param Payroll $payroll
     * @return Payroll
     */
    public function payPayroll(Payroll $payroll): Payroll
    {
        $payroll->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        return $payroll;
    }
    /**
     * Find payroll by ID or UUID.
     *
     * @param string|int $id
     * @return Payroll|null
     */
    public function findPayroll(string|int $id): ?Payroll
    {
        if (is_numeric($id)) {
            return Payroll::with(['employee.user', 'payrollPeriod', 'items'])->find($id);
        }
        if (Str::isUuid($id)) {
            return Payroll::with(['employee.user', 'payrollPeriod', 'items'])->where('uuid', $id)->first();
        }
        return null;
    }

    /**
     * Find payroll period by ID or UUID.
     *
     * @param string|int $id
     * @return PayrollPeriod|null
     */
    public function findPayrollPeriod(string|int $id): ?PayrollPeriod
    {
        if (is_numeric($id)) {
            return PayrollPeriod::find($id);
        }
        if (Str::isUuid($id)) {
            return PayrollPeriod::where('uuid', $id)->first();
        }
        return null;
    }

    /**
     * Batch approve payrolls.
     *
     * @param array $payrollUuids
     * @return int
     */
    public function batchApprovePayrolls(array $payrollUuids): int
    {
        return Payroll::whereIn('uuid', $payrollUuids)
            ->where('status', 'draft')
            ->update(['status' => 'approved']);
    }

    /**
     * Batch pay payrolls.
     *
     * @param array $payrollUuids
     * @return int
     */
    public function batchPayPayrolls(array $payrollUuids): int
    {
        return Payroll::whereIn('uuid', $payrollUuids)
            ->where('status', '!=', 'paid')
            ->update([
                'status' => 'paid',
                'payment_date' => now(),
            ]);
    }

    /**
     * Generate Payslip PDF.
     *
     * @param Payroll $payroll
     * @return \Illuminate\Http\Response
     */
    public function generatePayslipPdf(Payroll $payroll)
    {
        $payroll->load(['employee.user', 'employee.department', 'employee.designation', 'payrollPeriod', 'items']);
        
        $pdf = Pdf::loadView('hrm.payslip', compact('payroll'));
        
        return $pdf->download("payslip-{$payroll->employee->user->name}-{$payroll->payrollPeriod->name}.pdf");
    }
}

