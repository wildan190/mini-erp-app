<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\Reimbursement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReimbursementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_reimbursement_claim()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-CLAIM-' . uniqid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('receipt.jpg');

        $data = [
            'type' => 'medical',
            'amount' => 150000,
            'description' => 'Consultation fee',
            'proof_file' => $file,
        ];

        $response = $this->postJson('/api/platform/hrm/reimbursements', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['amount' => "150000.00"]);

        $this->assertDatabaseHas('reimbursements', [
            'employee_id' => $employee->id,
            'type' => 'medical',
            'amount' => 150000,
            'status' => 'pending',
        ]);

        // Assert file was stored
        $path = $response->json('data.proof_file');
        Storage::disk('public')->assertExists($path);
    }

    public function test_can_list_my_claims()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $employee = Employee::create([
            'user_id' => $user->id,
            'emp_code' => 'EMP-CLAIM-' . uniqid(),
            'status' => 'active',
        ]);

        Reimbursement::create([
            'employee_id' => $employee->id,
            'type' => 'travel',
            'amount' => 500000,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/platform/hrm/reimbursements/my-claims');

        $response->assertStatus(200)
            // ->assertJsonPath('data.0.type', 'travel');
            ->assertJsonFragment(['type' => 'travel'])
            ->assertJsonCount(13, 'data'); // Seeder creates 12 + 1 new one
    }

    public function test_can_update_reimbursement_status()
    {
        $manager = User::factory()->create();
        $this->actingAs($manager, 'sanctum');

        $employeeUser = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'emp_code' => 'EMP-CLAIM-' . uniqid(),
            'status' => 'active',
        ]);

        $reimbursement = Reimbursement::create([
            'employee_id' => $employee->id,
            'type' => 'medical',
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/platform/hrm/reimbursements/{$reimbursement->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'approved']);

        $this->assertDatabaseHas('reimbursements', [
            'id' => $reimbursement->id,
            'status' => 'approved',
            'approved_by' => $manager->id,
        ]);
    }
}
