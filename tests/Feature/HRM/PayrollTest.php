<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\PayrollPeriod;
use App\Models\HRM\SalaryComponent;
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
        SalaryComponent::create([
            'name' => 'Transport Allowance',
            'type' => 'earning',
            'is_fixed' => true,
            'value' => 500000,
        ]);

        // Create Payroll Period
        $period = PayrollPeriod::create([
            'name' => 'February 2026',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
        ]);

        // Generate Payroll
        $response = $this->postJson('/api/platform/hrm/payroll-periods/generate', [
            'payroll_period_id' => $period->id,
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
}
