<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Employee;
use App\Models\HRM\OfficeLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficeLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_can_list_office_locations()
    {
        $user = User::factory()->create();
        OfficeLocation::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/platform/hrm/office-locations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'address', 'latitude', 'longitude', 'radius', 'is_active']
                    ]
                ]
            ]);
    }

    public function test_can_create_office_location()
    {
        $user = User::factory()->create();

        $locationData = [
            'name' => 'Jakarta Head Office',
            'address' => 'Jl. Sudirman No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
            'is_active' => true,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/office-locations', $locationData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Office location created successfully',
            ]);

        $this->assertDatabaseHas('office_locations', [
            'name' => 'Jakarta Head Office',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
    }

    public function test_can_update_office_location()
    {
        $user = User::factory()->create();
        $location = OfficeLocation::factory()->create([
            'name' => 'Old Office',
            'radius' => 100,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/platform/hrm/office-locations/{$location->id}", [
                'name' => 'New Office',
                'radius' => 200,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('office_locations', [
            'id' => $location->id,
            'name' => 'New Office',
            'radius' => 200,
        ]);
    }

    public function test_can_delete_office_location()
    {
        $user = User::factory()->create();
        $location = OfficeLocation::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/platform/hrm/office-locations/{$location->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('office_locations', ['id' => $location->id]);
    }

    public function test_validates_gps_coordinates()
    {
        $user = User::factory()->create();

        // Invalid latitude (> 90)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/office-locations', [
                'name' => 'Test Office',
                'address' => 'Test Address',
                'latitude' => 95,
                'longitude' => 106.8456,
                'radius' => 100,
            ]);

        $response->assertStatus(422);

        // Invalid longitude (< -180)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/platform/hrm/office-locations', [
                'name' => 'Test Office',
                'address' => 'Test Address',
                'latitude' => -6.2088,
                'longitude' => -185,
                'radius' => 100,
            ]);

        $response->assertStatus(422);
    }
}
