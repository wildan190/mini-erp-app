<?php

namespace Tests\Feature\HRM;

use App\Models\HRM\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_shift()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'Morning Shift ' . uniqid(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];

        $response = $this->postJson('/api/platform/hrm/shifts', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_can_list_shifts()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Shift::create([
            'name' => 'Night Shift ' . uniqid(),
            'start_time' => '18:00',
            'end_time' => '02:00',
        ]);

        $response = $this->getJson('/api/platform/hrm/shifts');

        $response->assertStatus(200);
    }
}
