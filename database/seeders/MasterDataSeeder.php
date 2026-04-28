<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\HRM\Department;
use App\Models\HRM\Designation;
use App\Models\HRM\Employee;
use App\Models\HRM\Shift;
use App\Models\HRM\OfficeLocation;
use App\Models\HRM\SalaryComponent;
use App\Models\HRM\PayrollPeriod;
use App\Models\HRM\Attendance;
use App\Models\HRM\LeaveType;
use App\Models\HRM\LeaveRequest;
use App\Models\CRM\Customer;
use App\Models\CRM\Lead;
use App\Models\CRM\Prospect;
use App\Models\Finance\Account;
use App\Models\Finance\FinancialRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@erp.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create HRM Structure
        $departments = Department::factory(5)->create();
        $designations = Designation::factory(10)->create();
        $shifts = Shift::factory(3)->create();
        $locations = OfficeLocation::factory(2)->create();
        
        // 3. Create Employees
        // Create 20 random employees
        $employees = Employee::factory(20)->create()->each(function ($employee) use ($departments, $designations, $shifts) {
            $employee->update([
                'department_id' => $departments->random()->id,
                'designation_id' => $designations->random()->id,
                'shift_id' => $shifts->random()->id,
            ]);
        });

        // 4. Salary Components
        $earnings = [
            ['name' => 'Transport Allowance', 'type' => 'earning', 'value' => 500000, 'is_fixed' => true],
            ['name' => 'Meal Allowance', 'type' => 'earning', 'value' => 300000, 'is_fixed' => true],
            ['name' => 'Health Allowance', 'type' => 'earning', 'value' => 200000, 'is_fixed' => true],
        ];
        
        $deductions = [
            ['name' => 'BPJS Kesehatan', 'type' => 'deduction', 'value' => 1, 'is_fixed' => false, 'percentage_of' => 'basic_salary'],
            ['name' => 'BPJS Ketenagakerjaan', 'type' => 'deduction', 'value' => 2, 'is_fixed' => false, 'percentage_of' => 'basic_salary'],
        ];

        foreach (array_merge($earnings, $deductions) as $compData) {
            SalaryComponent::create($compData);
        }

        $allComponents = SalaryComponent::all();

        // Assign random components to employees
        foreach ($employees as $employee) {
            $assigned = $allComponents->random(rand(2, 4));
            foreach ($assigned as $comp) {
                $employee->salaryComponents()->attach($comp->id, [
                    'custom_value' => $this->shouldOverride() ? $comp->value * 1.1 : null
                ]);
            }
        }

        // 5. Payroll Periods & Attendance
        $periods = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $periods[] = PayrollPeriod::create([
                'name' => $date->format('F Y'),
                'start_date' => $date->copy()->startOfMonth()->toDateString(),
                'end_date' => $date->copy()->endOfMonth()->toDateString(),
                'status' => $i > 0 ? 'closed' : 'draft',
            ]);
        }

        // Generate attendance for the current period (last period)
        $currentPeriod = end($periods);
        $startDate = Carbon::parse($currentPeriod->start_date);
        $endDate = Carbon::parse($currentPeriod->end_date);

        foreach ($employees as $employee) {
            $loopDate = $startDate->copy();
            while ($loopDate <= $endDate) {
                if (!$loopDate->isWeekend()) {
                    // 90% chance of being present
                    if (rand(1, 100) <= 90) {
                        Attendance::factory()->create([
                            'employee_id' => $employee->id,
                            'date' => $loopDate->toDateString(),
                            'clock_in' => $loopDate->copy()->setTime(rand(7, 9), rand(0, 59)),
                            'clock_out' => $loopDate->copy()->setTime(17, rand(0, 30)),
                        ]);
                    }
                }
                $loopDate->addDay();
            }
        }

        // 6. CRM Data
        $customers = Customer::factory(15)->create();
        Lead::factory(20)->create();
        
        foreach ($customers as $customer) {
            if (rand(1, 100) <= 40) {
                Prospect::factory()->create(['customer_id' => $customer->id]);
            }
        }

        // 7. Finance Data
        $accounts = [
            ['code' => '1101', 'name' => 'Cash in Hand', 'type' => 'asset'],
            ['code' => '1102', 'name' => 'Bank Mandiri', 'type' => 'asset'],
            ['code' => '4101', 'name' => 'Sales Revenue', 'type' => 'revenue'],
            ['code' => '5101', 'name' => 'Salary Expense', 'type' => 'expense'],
            ['code' => '5102', 'name' => 'Office Rent', 'type' => 'expense'],
        ];

        foreach ($accounts as $acc) {
            Account::create($acc);
        }

        $allAccounts = Account::all();
        foreach ($allAccounts as $account) {
            FinancialRecord::factory(10)->create();
        }
    }

    private function shouldOverride(): bool
    {
        return rand(1, 100) <= 20; // 20% chance to override
    }
}
