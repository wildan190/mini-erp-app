<?php

namespace Tests\Feature\CRM;

use App\Models\User;
use App\Models\CRM\Prospect;
use App\Models\CRM\Customer;
use App\Models\CRM\SalesPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SalesPipelineEnterpriseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_log_pipeline_transition_with_enterprise_fields()
    {
        $customer = Customer::create([
            'name' => 'Acme Corp',
            'email' => 'acme@example.com',
            'customer_type' => 'corporate',
            'currency' => 'IDR'
        ]);

        $prospect = Prospect::create([
            'customer_id' => $customer->id,
            'title' => 'Big Deal',
            'status' => 'new'
        ]);

        $data = [
            'prospect_id' => $prospect->uuid,
            'stage' => 'qualified',
            'notes' => 'Customer is very interested in the proposal.'
        ];

        $response = $this->postJson('/api/platform/crm/sales-pipeline', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sales_pipelines', [
            'stage' => 'qualified',
            'notes' => 'Customer is very interested in the proposal.',
            'user_id' => auth()->id()
        ]);

        $response->assertJsonPath('data.stage', 'qualified');
    }

    public function test_can_get_pipeline_detail_by_uuid()
    {
        $customer = Customer::create(['name' => 'A', 'email' => 'a@b.com', 'currency' => 'IDR']);
        $prospect = Prospect::create(['customer_id' => $customer->id, 'title' => 'T', 'status' => 'S']);
        $pipeline = SalesPipeline::create([
            'prospect_id' => $prospect->id,
            'stage' => 'initial',
            'user_id' => auth()->id()
        ]);

        $response = $this->getJson("/api/platform/crm/sales-pipeline/{$pipeline->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.uuid', $pipeline->uuid);
    }

    public function test_cannot_log_transition_with_invalid_uuid()
    {
        $data = [
            'prospect_id' => '12345', // Invalid UUID
            'stage' => 'qualified'
        ];

        $response = $this->postJson('/api/platform/crm/sales-pipeline', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['prospect_id']);
    }
}
