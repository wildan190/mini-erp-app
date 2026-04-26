<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Finance\Account;
use Database\Seeders\EnterpriseFinanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed first so initial data is available
        $this->seed(EnterpriseFinanceSeeder::class);
        $this->user = User::factory()->create();
    }

    public function test_ledger_accounts_requires_auth()
    {
        $response = $this->getJson('/api/platform/finance/ledger/accounts');
        $response->assertStatus(401);
    }

    public function test_can_list_ledger_accounts()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/platform/finance/ledger/accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['uuid', 'code', 'name', 'type']
                ]
            ]);
    }

    public function test_reporting_balance_sheet()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/platform/finance/reporting/balance-sheet');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => ['assets', 'liabilities', 'equity', 'total_assets']
            ]);
    }

    public function test_reporting_cash_flow()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/platform/finance/reporting/cash-flow');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['operating_activities', 'net_cash_flow', 'details']
            ]);
    }

    public function test_ai_suggest_account()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/platform/finance/ai/suggest-account', [
                'description' => 'Payment for office rent'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['account_uuid', 'account_name', 'confidence']
            ]);
    }

    public function test_ai_budget_variance()
    {
        $account = Account::where('code', '6300')->first();
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/platform/finance/ai/budget-variance/{$account->uuid}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'budget_amount',
                    'current_spend',
                    'predicted_month_end',
                    'variance_probability',
                    'confidence_score'
                ]
            ]);
    }
}
