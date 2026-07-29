<?php

namespace Tests\Unit\HRM;

use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\Payroll;
use App\Domain\HRM\Models\PayrollPeriod;
use App\Domain\HRM\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PayrollService $payrollService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payrollService = new PayrollService();
    }

    public function test_batch_approve_payrolls()
    {
        // 1. Arrange: Create some draft payrolls
        $period = PayrollPeriod::create([
            'name' => 'March 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
        ]);

        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        $payroll1 = Payroll::create([
            'employee_id' => $employee1->id,
            'payroll_period_id' => $period->id,
            'basic_salary' => 5000000,
            'net_salary' => 5000000,
            'status' => 'draft',
        ]);

        $payroll2 = Payroll::create([
            'employee_id' => $employee2->id,
            'payroll_period_id' => $period->id,
            'basic_salary' => 6000000,
            'net_salary' => 6000000,
            'status' => 'draft',
        ]);

        $uuids = [$payroll1->uuid, $payroll2->uuid];

        // 2. Act: Call the batch approve method (which doesn't exist yet)
        $count = $this->payrollService->batchApprovePayrolls($uuids);

        // 3. Assert: Verify counts and statuses
        $this->assertEquals(2, $count);
        $this->assertEquals('approved', $payroll1->fresh()->status);
        $this->assertEquals('approved', $payroll2->fresh()->status);
    }

    public function test_batch_approve_only_draft_payrolls()
    {
        // 1. Arrange: Create a draft and a paid payroll
        $period = PayrollPeriod::create([
            'name' => 'March 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
        ]);

        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        $payrollDraft = Payroll::create([
            'employee_id' => $employee1->id,
            'payroll_period_id' => $period->id,
            'basic_salary' => 5000000,
            'net_salary' => 5000000,
            'status' => 'draft',
        ]);

        $payrollPaid = Payroll::create([
            'employee_id' => $employee2->id,
            'payroll_period_id' => $period->id,
            'basic_salary' => 6000000,
            'net_salary' => 6000000,
            'status' => 'paid',
        ]);

        $uuids = [$payrollDraft->uuid, $payrollPaid->uuid];

        // 2. Act
        $count = $this->payrollService->batchApprovePayrolls($uuids);

        // 3. Assert
        $this->assertEquals(1, $count); // Only 1 should be updated
        $this->assertEquals('approved', $payrollDraft->fresh()->status);
        $this->assertEquals('paid', $payrollPaid->fresh()->status); // Should remain 'paid'
    }
}
