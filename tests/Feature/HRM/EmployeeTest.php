<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Department;
use App\Models\HRM\Designation;
use App\Models\HRM\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase; // Commented out to avoid wiping existing data if not using a separate test DB

    public function test_can_list_departments()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/platform/hrm/departments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);
    }

    public function test_can_create_department()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'IT Department ' . uniqid(),
            'description' => 'Information Technology',
        ];

        $response = $this->postJson('/api/platform/hrm/departments', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_can_list_employees()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/platform/hrm/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);
    }

    public function test_can_create_employee()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $newUser = User::factory()->create();
        $department = Department::create(['name' => 'HR ' . uniqid(), 'description' => 'Human Resources']);
        $designation = Designation::create(['name' => 'Manager ' . uniqid(), 'description' => 'Manager']);

        $data = [
            'user_id' => $newUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'emp_code' => 'EMP-' . uniqid(),
            'joining_date' => '2023-01-01',
            'status' => 'active',
            'nik' => '1234567890123456',
            'place_of_birth' => 'Jakarta',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'marital_status' => 'single',
            'phone' => '081234567890',
        ];

        $response = $this->postJson('/api/platform/hrm/employees', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['emp_code' => $data['emp_code']])
            ->assertJsonFragment(['nik' => $data['nik']]);
    }

    public function test_can_create_employee_with_new_user_account()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $email = 'newemployee' . uniqid() . '@example.com';
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $email,
            'password' => 'password123',
            'emp_code' => 'EMP-NEW-' . uniqid(),
            'status' => 'active',
        ];

        $response = $this->postJson('/api/platform/hrm/employees', $data);

        $response->assertStatus(201);

        // Verify User was created
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'John Doe',
        ]);

        // Verify Employee was created and linked to the new User
        $user = User::where('email', $email)->first();
        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'emp_code' => $data['emp_code'],
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }
}
