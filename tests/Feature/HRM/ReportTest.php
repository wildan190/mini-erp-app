<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Department;
use App\Models\HRM\Employee;
use App\Models\HRM\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_turnover_report()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create employees joined this year
        Employee::factory()->count(5)->create([
            'joining_date' => now()->startOfYear(),
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/platform/hrm/reports/turnover');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['period', 'joined', 'resigned', 'terminated', 'total_leavers', 'net_change']]);
    }

    public function test_can_get_labor_cost_report()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $dept = Department::create(['name' => 'IT', 'description' => 'Info Tech']);
        $employee = Employee::create([
            'user_id' => User::factory()->create()->id,
            'department_id' => $dept->id,
            'status' => 'active',
            'emp_code' => 'EMP-TEST-' . uniqid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $period = \App\Models\HRM\PayrollPeriod::create([
            'name' => 'Jan 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'closed',
        ]);

        Payroll::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary' => 5000000,
            'net_salary' => 4500000,
            'total_earnings' => 5000000,
            'total_deductions' => 500000,
            'status' => 'paid',
        ]);

        $response = $this->getJson('/api/platform/hrm/reports/labor-cost');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['year', 'monthly_costs', 'department_costs', 'total_year_cost']]);
    }
}
