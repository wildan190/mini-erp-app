<?php

namespace App\Services\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\Payroll;
use App\Models\HRM\PayrollItem;
use App\Models\HRM\PayrollPeriod;
use App\Models\HRM\SalaryComponent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
            ->when(isset($filters['payroll_period_id']), function (Builder $query) use ($filters) {
                $query->where('payroll_period_id', $filters['payroll_period_id']);
            })
            ->when(isset($filters['employee_id']), function (Builder $query) use ($filters) {
                $query->where('employee_id', $filters['employee_id']);
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

        $employees = Employee::where('status', 'active')->get();
        $count = 0;

        foreach ($employees as $employee) {
            // Check if payroll already exists for this period
            if (Payroll::where('employee_id', $employee->id)->where('payroll_period_id', $period->id)->exists()) {
                continue;
            }

            $this->calculateSalary($employee, $period);
            $count++;
        }

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

            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'payroll_period_id' => $period->id,
                'basic_salary' => $basicSalary,
                'status' => 'draft',
            ]);

            // Add Basic Salary Item
            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'name' => 'Basic Salary',
                'amount' => $basicSalary,
                'type' => 'earning',
            ]);

            $components = SalaryComponent::where('is_active', true)->get();

            foreach ($components as $component) {
                $amount = 0;

                if ($component->is_fixed) {
                    $amount = $component->value;
                } elseif ($component->percentage_of === 'basic_salary') {
                    $amount = ($basicSalary * $component->value) / 100;
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
}
