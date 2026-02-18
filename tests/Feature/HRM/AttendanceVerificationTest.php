<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Attendance;
use App\Models\HRM\Employee;
use App\Models\HRM\OfficeLocation;
use App\Models\HRM\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_can_clock_in_with_face_and_location_verification()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'face_encoding' => 'test_encoding',
            'face_image_path' => 'faces/enrolled/employee.jpg',
            'requires_face_verification' => true,
        ]);

        $officeLocation = OfficeLocation::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
        ]);

        $shift = Shift::factory()->create();
        $employee->update(['shift_id' => $shift->id]);

        $faceImage = UploadedFile::fake()->image('current_face.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/attendances/clock-in', [
                'face_image' => $faceImage,
                'office_location_id' => $officeLocation->id,
                'latitude' => -6.2088, // Same as office
                'longitude' => 106.8456, // Same as office
                'notes' => 'Clock in with verification',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Clocked in successfully',
            ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'face_verification_status' => 'verified',
            'location_verification_status' => 'within_radius',
            'office_location_id' => $officeLocation->id,
        ]);

        $attendance = Attendance::where('employee_id', $employee->id)->first();
        $this->assertNotNull($attendance->face_image_path);
    }

    public function test_cannot_clock_in_when_face_required_but_not_provided()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'requires_face_verification' => true,
            'face_encoding' => 'test_encoding',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/attendances/clock-in', [
                'notes' => 'Trying without face',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Face verification is required but no face image provided.',
            ]);
    }

    public function test_cannot_clock_in_outside_office_radius()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $officeLocation = OfficeLocation::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100, // 100 meters
        ]);

        // Location far away (> 100 meters)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/attendances/clock-in', [
                'office_location_id' => $officeLocation->id,
                'latitude' => -6.2200, // ~1.2 km away
                'longitude' => 106.8600,
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', fn($message) => str_contains($message, 'outside the office radius'));
    }

    public function test_can_clock_in_without_verification_when_not_required()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'requires_face_verification' => false,
        ]);

        $shift = Shift::factory()->create();
        $employee->update(['shift_id' => $shift->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/attendances/clock-in', [
                'notes' => 'Simple clock in',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'face_verification_status' => 'skipped',
            'location_verification_status' => 'skipped',
        ]);
    }

    public function test_can_clock_out_with_location_verification()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $officeLocation = OfficeLocation::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
        ]);

        // Create existing attendance
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => null,
            'office_location_id' => $officeLocation->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/attendances/clock-out', [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'notes' => 'Clock out',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Clocked out successfully',
            ]);

        $attendance = Attendance::where('employee_id', $employee->id)->first();
        $this->assertNotNull($attendance->clock_out);
        $this->assertEquals(-6.2088, (float) $attendance->check_out_latitude);
        $this->assertEquals(106.8456, (float) $attendance->check_out_longitude);
    }

    public function test_cannot_clock_in_twice_on_same_day()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/attendances/clock-in');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Employee already clocked in for today.',
            ]);
    }

    public function test_location_verification_validates_coordinates_within_radius()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $officeLocation = OfficeLocation::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 50, // 50 meters radius
        ]);

        // Location within 50 meters (approximately)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/attendances/clock-in', [
                'office_location_id' => $officeLocation->id,
                'latitude' => -6.20884, // Very close
                'longitude' => 106.84565, // Very close
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'location_verification_status' => 'within_radius',
        ]);
    }
}
