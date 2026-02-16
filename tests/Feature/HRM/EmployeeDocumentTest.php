<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Department;
use App\Models\HRM\Designation;
use App\Models\HRM\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_document()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employeeUser = User::factory()->create();
        $department = Department::create(['name' => 'IT ' . uniqid()]);
        $designation = Designation::create(['name' => 'Dev ' . uniqid()]);

        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'emp_code' => 'EMP-TEST',
            'status' => 'active'
        ]);

        $file = UploadedFile::fake()->create('contract.pdf', 100);

        $data = [
            'type' => 'contract',
            'file' => $file,
            'expiry_date' => '2025-01-01',
        ];

        $response = $this->postJson("/api/platform/hrm/employees/{$employee->id}/documents", $data);

        $response->assertStatus(201);

        // Assert file exists
        $document = $response->json('data');
        Storage::disk('public')->assertExists($document['file_path']);
    }

    public function test_can_list_documents()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employeeUser = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'emp_code' => 'EMP-TEST-LIST',
        ]);

        $response = $this->getJson("/api/platform/hrm/employees/{$employee->id}/documents");

        $response->assertStatus(200);
    }
}
