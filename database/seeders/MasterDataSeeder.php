<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Domain\HRM\Models\Department;
use App\Domain\HRM\Models\Designation;
use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\Shift;
use App\Domain\HRM\Models\OfficeLocation;
use App\Domain\HRM\Models\SalaryComponent;
use App\Domain\HRM\Models\PayrollPeriod;
use App\Domain\HRM\Models\Attendance;
use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\Lead;
use App\Domain\CRM\Models\Prospect;
use App\Domain\Finance\Models\Account;
use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\PurchaseRequest;
use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Purchasing\Models\PurchaseInvoice;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectTask;
use App\Domain\Project\Models\ProjectTimesheet;
use App\Domain\Project\Models\ProjectCost;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed RBAC Roles & Approval Chains
        $this->call([
            RolePermissionSeeder::class,
            ApprovalChainSeeder::class,
        ]);

        // Ensure Passport Personal Access Client exists
        if (\Illuminate\Support\Facades\Schema::hasTable('oauth_clients')) {
            $hasPersonalClient = \Illuminate\Support\Facades\DB::table('oauth_clients')
                ->where('grant_types', 'like', '%personal_access%')
                ->exists();

            if (!$hasPersonalClient) {
                \Illuminate\Support\Facades\Artisan::call('passport:client', [
                    '--personal' => true,
                    '--name' => 'MiniERP Personal Access Client',
                    '--provider' => 'users',
                    '--no-interaction' => true,
                ]);
            }
        }

        // 1. Create Default Admin User & Assign Super Admin Role
        $admin = User::updateOrCreate(
            ['email' => 'admin@erp.com'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super-admin');

        // 2. HRM Domain Seeding
        $departments  = Department::factory(5)->create();
        $designations = Designation::factory(10)->create();
        $shifts       = Shift::factory(3)->create();
        $locations    = OfficeLocation::factory(2)->create();

        $employees = Employee::factory(20)->create()->each(function ($employee) use ($departments, $designations, $shifts) {
            $employee->update([
                'department_id'  => $departments->random()->id,
                'designation_id' => $designations->random()->id,
                'shift_id'       => $shifts->random()->id,
            ]);
        });

        // Salary Components
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
        foreach ($employees as $employee) {
            $assigned = $allComponents->random(rand(2, 4));
            foreach ($assigned as $comp) {
                $employee->salaryComponents()->attach($comp->id, [
                    'custom_value' => $this->shouldOverride() ? $comp->value * 1.1 : null
                ]);
            }
        }

        // Payroll Periods & Attendance
        $periods = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $periods[] = PayrollPeriod::create([
                'name'       => $date->format('F Y'),
                'start_date' => $date->copy()->startOfMonth()->toDateString(),
                'end_date'   => $date->copy()->endOfMonth()->toDateString(),
                'status'     => $i > 0 ? 'closed' : 'draft',
            ]);
        }

        $currentPeriod = end($periods);
        $startDate     = Carbon::parse($currentPeriod->start_date);
        $endDate       = Carbon::parse($currentPeriod->end_date);

        foreach ($employees as $employee) {
            $loopDate = $startDate->copy();
            while ($loopDate <= $endDate) {
                if (!$loopDate->isWeekend()) {
                    if (rand(1, 100) <= 90) {
                        Attendance::factory()->create([
                            'employee_id' => $employee->id,
                            'date'        => $loopDate->toDateString(),
                            'clock_in'    => $loopDate->copy()->setTime(rand(7, 9), rand(0, 59)),
                            'clock_out'   => $loopDate->copy()->setTime(17, rand(0, 30)),
                        ]);
                    }
                }
                $loopDate->addDay();
            }
        }

        // 3. CRM Domain Seeding
        $customers = Customer::factory(15)->create();
        Lead::factory(20)->create();

        foreach ($customers as $customer) {
            if (rand(1, 100) <= 40) {
                Prospect::factory()->create(['customer_id' => $customer->id]);
            }
        }

        // 4. Finance Domain Seeding
        $accounts = [
            ['code' => '1101', 'name' => 'Cash in Hand', 'type' => 'asset'],
            ['code' => '1102', 'name' => 'Bank Mandiri', 'type' => 'asset'],
            ['code' => '4101', 'name' => 'Sales Revenue', 'type' => 'revenue'],
            ['code' => '5101', 'name' => 'Salary Expense', 'type' => 'expense'],
            ['code' => '5102', 'name' => 'Office Rent', 'type' => 'expense'],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(['code' => $acc['code']], $acc);
        }

        $allAccounts = Account::all();
        foreach ($allAccounts as $account) {
            FinancialRecord::factory(10)->create();
        }

        // 5. Purchasing Domain Seeding
        $suppliers = Supplier::factory(10)->create();

        foreach ($suppliers as $supplier) {
            // Purchase Requests
            PurchaseRequest::factory(2)->create([
                'requestor_id' => $admin->id,
            ]);

            // Purchase Orders with items
            $pos = PurchaseOrder::factory(2)->create([
                'supplier_id' => $supplier->id,
            ]);

            foreach ($pos as $po) {
                $qty   = rand(1, 50);
                $price = rand(50000, 2000000);
                $po->items()->create([
                    'item_name'       => 'Item ' . Str::random(5),
                    'qty'             => $qty,
                    'price'           => $price,
                    'tax_rate'        => 11,
                    'discount'        => 0,
                    'total'           => $qty * $price,
                ]);
            }

            // Purchase Invoices
            $invoices = PurchaseInvoice::factory(2)->create([
                'supplier_id' => $supplier->id,
            ]);

            foreach ($invoices as $inv) {
                $qty   = rand(1, 10);
                $price = rand(100000, 5000000);
                $inv->items()->create([
                    'item_name' => 'Invoice Item ' . Str::random(5),
                    'qty'       => $qty,
                    'price'     => $price,
                    'total'     => $qty * $price,
                ]);
            }
        }

        // 6. Project Management Domain Seeding
        $projects = Project::factory(5)->create();

        foreach ($projects as $project) {
            // Project Tasks & Subtasks
            $tasks = ProjectTask::factory(4)->create([
                'project_uuid'           => $project->uuid,
                'assigned_employee_uuid' => $employees->random()->uuid ?? null,
            ]);

            foreach ($tasks->take(2) as $parentTask) {
                ProjectTask::factory(2)->create([
                    'project_uuid'     => $project->uuid,
                    'parent_task_uuid' => $parentTask->uuid,
                ]);
            }

            // Project Members
            foreach ($employees->random(3) as $memberEmp) {
                $project->members()->create([
                    'employee_uuid'         => $memberEmp->uuid,
                    'role'                  => rand(0, 1) ? 'Developer' : 'Project Manager',
                    'allocation_percentage' => rand(50, 100),
                ]);
            }

            // Timesheets
            foreach ($employees->random(2) as $tsEmp) {
                ProjectTimesheet::factory(3)->create([
                    'project_uuid'  => $project->uuid,
                    'employee_uuid' => $tsEmp->uuid,
                ]);
            }

            // Project Costs
            ProjectCost::factory(3)->create([
                'project_uuid' => $project->uuid,
            ]);
        }
    }

    private function shouldOverride(): bool
    {
        return rand(1, 100) <= 20;
    }
}
