<?php

namespace Tests\Feature\CRM;

use App\Models\User;
use App\Models\CRM\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class CustomerEnterpriseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_create_enterprise_customer()
    {
        $data = [
            'name' => 'Acme Corp',
            'email' => 'contact@acme.com',
            'company_name' => 'Acme Corporation Ltd.',
            'customer_type' => 'corporate',
            'tax_id' => '12.345.678.9-012.000',
            'industry' => 'Manufacturing',
            'website' => 'https://acme.com',
            'phone' => '+62215551234',
            'billing_address' => 'Sudirman Central Business District',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
            'country' => 'Indonesia',
            'credit_limit' => 50000000,
            'payment_terms' => 'Net 30',
            'currency' => 'IDR',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/platform/crm/customers', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('customers', [
            'email' => 'contact@acme.com',
            'tax_id' => '12.345.678.9-012.000',
            'credit_limit' => '50000000.00'
        ]);

        $response->assertJsonPath('data.company_name', 'Acme Corporation Ltd.');
    }

    public function test_validation_works_for_enterprise_fields()
    {
        $data = [
            'name' => '', // Should fail
            'email' => 'invalid-email', // Should fail
            'customer_type' => 'alien', // Should fail
            'status' => 'unknown' // Should fail
        ];

        $response = $this->postJson('/api/platform/crm/customers', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'customer_type', 'status']);
    }
}
