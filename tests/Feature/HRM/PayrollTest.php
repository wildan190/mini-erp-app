<?php

namespace Tests\Feature\HRM;

use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\PayrollPeriod;
use App\Domain\HRM\Models\SalaryComponent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_salary_component()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'Transport Allowance',
            'type' => 'earning',
            'is_fixed' => true,
            'value' => 500000,
            'is_taxable' => true,
        ];

        $response = $this->postJson('/api/platform/hrm/salary-components', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_can_create_payroll_period()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ];

        $response = $this->postJson('/api/platform/hrm/payroll-periods', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_can_generate_payroll()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create Employee
        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-PAY-' . uniqid(),
            'basic_salary' => 5000000,
            'status' => 'active',
        ]);

        // Create Salary Component
        $component = SalaryComponent::create([
            'name' => 'Transport Allowance',
            'type' => 'earning',
            'is_fixed' => true,
            'value' => 500000,
        ]);

        $employee->salaryComponents()->attach($component->id);

        // Create Payroll Period
        $period = PayrollPeriod::create([
            'name' => 'February 2026',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
        ]);

        // Create Attendance to avoid absence deduction
        $start = Carbon::parse($period->start_date);
        $end = Carbon::parse($period->end_date);
        while ($start <= $end) {
            if (!$start->isWeekend()) {
                \App\Domain\HRM\Models\Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $start->format('Y-m-d'),
                    'clock_in' => '08:00:00',
                    'clock_out' => '17:00:00',
                    'status' => 'present',
                ]);
            }
            $start->addDay();
        }

        // Generate Payroll
        $response = $this->postJson('/api/platform/hrm/payroll-periods/generate', [
            'payroll_period_uuid' => $period->uuid,
        ]);

        $response->assertStatus(200);

        // Check Payroll Record
        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'status' => 'draft',
            'net_salary' => 5500000, // 5M + 500k
        ]);

        // Check Payroll Items
        $this->assertDatabaseHas('payroll_items', [
            'name' => 'Basic Salary',
            'amount' => 5000000,
        ]);

        $this->assertDatabaseHas('payroll_items', [
            'name' => 'Transport Allowance',
            'amount' => 500000,
        ]);
    }

    public function test_can_generate_payslip_pdf()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create Employee
        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-PDF-' . uniqid(),
            'basic_salary' => 5000000,
            'status' => 'active',
        ]);

        // Create Payroll Period
        $period = PayrollPeriod::create([
            'name' => 'March 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
        ]);

        // Generate Payroll
        $this->postJson('/api/platform/hrm/payroll-periods/generate', [
            'payroll_period_uuid' => $period->uuid,
        ]);

        $payroll = \App\Domain\HRM\Models\Payroll::where('employee_id', $employee->id)->first();

        // Get Payslip
        $response = $this->getJson("/api/platform/hrm/payrolls/{$payroll->uuid}/payslip");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_batch_pay_payrolls()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create 2 Employees
        $e1 = Employee::create(['user_id' => $user->id, 'emp_code' => 'B1', 'basic_salary' => 1000, 'status' => 'active']);
        $e2 = Employee::create(['user_id' => $user->id, 'emp_code' => 'B2', 'basic_salary' => 2000, 'status' => 'active']);

        $period = PayrollPeriod::create(['name' => 'Batch Period', 'start_date' => '2026-04-01', 'end_date' => '2026-04-30']);

        $this->postJson('/api/platform/hrm/payroll-periods/generate', ['payroll_period_uuid' => $period->uuid]);

        $payrolls = \App\Domain\HRM\Models\Payroll::whereIn('employee_id', [$e1->id, $e2->id])->get();
        $uuids = $payrolls->pluck('uuid')->toArray();

        $response = $this->postJson('/api/platform/hrm/payrolls/batch-pay', [
            'payroll_uuids' => $uuids,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payrolls', ['uuid' => $uuids[0], 'status' => 'paid']);
        $this->assertDatabaseHas('payrolls', ['uuid' => $uuids[1], 'status' => 'paid']);
    }
}


