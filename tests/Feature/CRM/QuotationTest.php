<?php

namespace Tests\Feature\CRM;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_create_quotation_with_auto_calculation()
    {
        $customer = Customer::create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $data = [
            'customer_id' => $customer->uuid,
            'valid_until' => now()->addDays(30)->toDateString(),
            'discount_amount' => 1000,
            'items' => [
                [
                    'description' => 'Product A',
                    'quantity' => 2,
                    'unit_price' => 5000,
                    'tax_rate' => 10
                ],
                [
                    'description' => 'Product B',
                    'quantity' => 1,
                    'unit_price' => 10000,
                    'tax_rate' => 0
                ]
            ]
        ];

        /*
         * Product A: 2 * 5000 = 10000. Tax 10% = 1000. Total = 11000.
         * Product B: 1 * 10000 = 10000. Tax 0% = 0. Total = 10000.
         * Subtotal: 10000 + 10000 = 20000.
         * Tax Amount: 1000 + 0 = 1000.
         * Discount: 1000.
         * Total Amount: 20000 + 1000 - 1000 = 20000.
         */

        $response = $this->postJson('/api/platform/crm/quotation', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.subtotal', '20000.00');
        $response->assertJsonPath('data.tax_amount', '1000.00');
        $response->assertJsonPath('data.total_amount', '20000.00');
        $this->assertCount(2, $response->json('data.items'));
    }
}
