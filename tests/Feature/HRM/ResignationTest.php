<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\Resignation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResignationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_resignation()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-RES-' . uniqid(),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'status' => 'active',
            'joining_date' => now()->subYear(),
        ]);

        $data = [
            'notice_date' => now()->toDateString(),
            'resignation_date' => now()->addMonth()->toDateString(),
            'reason' => 'Found a better opportunity',
        ];

        $response = $this->postJson('/api/platform/hrm/resignations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['reason' => 'Found a better opportunity']);

        $this->assertDatabaseHas('resignations', [
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_update_resignation_status_and_employee_status()
    {
        $manager = User::factory()->create();
        $this->actingAs($manager, 'sanctum');

        $employeeUser = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'emp_code' => 'EMP-RES-' . uniqid(),
            'first_name' => 'Mark',
            'last_name' => 'Smith',
            'status' => 'active',
            'joining_date' => now()->subYears(2),
        ]);

        // Resignation date is today, so employee status should update immediately upon approval
        $resignation = Resignation::create([
            'employee_id' => $employee->id,
            'notice_date' => now()->subMonth(),
            'resignation_date' => now(),
            'reason' => 'Personal reasons',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/platform/hrm/resignations/{$resignation->id}/status", [
            'status' => 'approved',
            'remarks' => 'Good luck!',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'approved']);

        $this->assertDatabaseHas('resignations', [
            'id' => $resignation->id,
            'status' => 'approved',
            'remarks' => 'Good luck!',
        ]);

        // Verify employee status is updated to 'resigned'
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'status' => 'resigned',
        ]);
    }
}
