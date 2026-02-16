<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_clock_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-ATT-' . uniqid(),
            'joining_date' => '2023-01-01',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/platform/hrm/attendances/clock-in', [
            'location_lat' => '-6.200000',
            'location_long' => '106.816666',
            'notes' => 'Test Clock In',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'status' => 'present',
        ]);
    }

    public function test_cannot_clock_in_twice_same_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-ATT-2-' . uniqid(),
            'status' => 'active',
        ]);

        $this->postJson('/api/platform/hrm/attendances/clock-in');
        $response = $this->postJson('/api/platform/hrm/attendances/clock-in');

        $response->assertStatus(400);
    }

    public function test_can_clock_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-ATT-3-' . uniqid(),
            'status' => 'active',
        ]);

        // Clock in first
        $this->postJson('/api/platform/hrm/attendances/clock-in');

        $response = $this->postJson('/api/platform/hrm/attendances/clock-out', [
            'notes' => 'Done for the day',
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($employee->attendances()->first()->clock_out);
    }
}
