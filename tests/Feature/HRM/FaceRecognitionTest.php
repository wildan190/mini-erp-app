<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaceRecognitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_can_enroll_employee_face()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
        ]);

        $faceImage = UploadedFile::fake()->image('face.jpg', 800, 800);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/platform/hrm/employees/{$employee->id}/enroll-face", [
                'face_image' => $faceImage,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Face enrolled successfully',
            ]);

        $employee->refresh();

        $this->assertNotNull($employee->face_image_path);
        $this->assertNotNull($employee->face_encoding);
        $this->assertEquals(1, $employee->requires_face_verification);
    }

    public function test_can_remove_employee_face_data()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'face_image_path' => 'faces/enrolled/test.jpg',
            'face_encoding' => 'test_encoding',
            'requires_face_verification' => true,
        ]);

        Storage::disk('public')->put('faces/enrolled/test.jpg', 'fake content');

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/platform/hrm/employees/{$employee->id}/face-data");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Face data removed successfully',
            ]);

        $employee->refresh();

        $this->assertNull($employee->face_image_path);
        $this->assertNull($employee->face_encoding);
        $this->assertEquals(0, $employee->requires_face_verification);
    }

    public function test_face_enrollment_validates_image_file()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        // Test with non-image file
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/platform/hrm/employees/{$employee->id}/enroll-face", [
                'face_image' => UploadedFile::fake()->create('document.pdf', 1000),
            ]);

        $response->assertStatus(422);
    }

    public function test_face_enrollment_validates_file_size()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        // Test with oversized image (> 5MB)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/platform/hrm/employees/{$employee->id}/enroll-face", [
                'face_image' => UploadedFile::fake()->image('large.jpg')->size(6000),
            ]);

        $response->assertStatus(422);
    }

    public function test_requires_face_image_for_enrollment()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/platform/hrm/employees/{$employee->id}/enroll-face", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['face_image']);
    }
}
