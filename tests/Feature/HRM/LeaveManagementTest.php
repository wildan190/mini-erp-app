<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_leave_type()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'Annual Leave ' . uniqid(),
            'days_allowed' => 12,
            'description' => 'Standard annual leave',
        ];

        $response = $this->postJson('/api/platform/hrm/leave-types', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_can_apply_for_leave()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-LEAVE-' . uniqid(),
            'status' => 'active',
        ]);

        $leaveType = LeaveType::create(['name' => 'Sick Leave', 'days_allowed' => 10]);

        $startDate = Carbon::tomorrow()->format('Y-m-d');
        $endDate = Carbon::tomorrow()->addDays(2)->format('Y-m-d');

        $response = $this->postJson('/api/platform/hrm/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Feeling unwell',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_apply_if_balance_insufficient()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-LEAVE-2-' . uniqid(),
            'status' => 'active',
        ]);

        $leaveType = LeaveType::create(['name' => 'Small Leave', 'days_allowed' => 1]);

        $startDate = Carbon::tomorrow()->format('Y-m-d');
        $endDate = Carbon::tomorrow()->addDays(5)->format('Y-m-d'); // 6 days requested

        $response = $this->postJson('/api/platform/hrm/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Long vacation',
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Insufficient leave balance. Remaining: 1 days.']);
    }

    public function test_can_approve_leave_request()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-LEAVE-3-' . uniqid(),
            'status' => 'active',
        ]);

        $leaveType = LeaveType::create(['name' => 'Annual Leave', 'days_allowed' => 12]);
        // Ensure balance exists
        $leaveBalance = \App\Models\HRM\LeaveBalance::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => now()->year,
            'total_days' => 12,
            'remaining_days' => 12,
        ]);

        $leaveRequest = \App\Models\HRM\LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(1),
            'reason' => 'Test',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/platform/hrm/leave-requests/{$leaveRequest->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
        ]);

        // Verify balance deduction
        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $employee->id,
            'remaining_days' => 10, // 12 - 2 days
        ]);
    }
}
