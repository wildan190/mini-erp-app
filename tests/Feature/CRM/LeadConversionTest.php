<?php

namespace Tests\Feature\CRM;

use App\Models\User;
use App\Models\CRM\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class LeadConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_convert_lead_to_prospect()
    {
        $lead = Lead::create([
            'lead_name' => 'Test Lead',
            'email' => 'test@lead.com',
            'phone' => '123456789',
            'company' => 'Lead Co',
            'source' => 'Web',
            'status' => 'new'
        ]);

        $response = $this->postJson("/api/platform/crm/leads/{$lead->uuid}/convert");

        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', [
            'email' => 'test@lead.com',
            'name' => 'Test Lead'
        ]);

        $this->assertDatabaseHas('prospects', [
            'title' => 'Opportunity from Test Lead'
        ]);

        $this->assertEquals('converted', $lead->fresh()->status);
    }

    public function test_can_convert_lead_without_email()
    {
        $lead = Lead::create([
            'lead_name' => 'Lead Without Email',
            'phone' => '999888777',
            'source' => 'Manual',
            'status' => 'new'
        ]);

        $response = $this->postJson("/api/platform/crm/leads/{$lead->uuid}/convert");

        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', [
            'name' => 'Lead Without Email',
            'email' => null
        ]);

        $this->assertEquals('converted', $lead->fresh()->status);
    }
}
