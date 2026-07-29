<?php

namespace Database\Seeders;

use App\Domain\Finance\Models\Account;
use App\Domain\Finance\Models\AnalyticalAccount;
use App\Domain\Finance\Models\Budget;
use App\Domain\Finance\Models\JournalEntry;
use App\Domain\Finance\Models\JournalItem;
use App\Domain\Finance\Services\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnterpriseFinanceSeeder extends Seeder
{
    protected $accounting;

    public function __construct(AccountingService $accounting)
    {
        $this->accounting = $accounting;
    }

    public function run()
    {
        // 1. Chart of Accounts (IFRS Standard Ranges)
        $coa = [
            // Assets (1000-1999)
            ['code' => '1010', 'name' => 'Cash & Bank', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1500', 'name' => 'Inventory', 'type' => 'asset'],
            
            // Liabilities (2000-2999)
            ['code' => '2100', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '2200', 'name' => 'Tax Payable (VAT)', 'type' => 'liability'],
            
            // Equity (3000-3999)
            ['code' => '3000', 'name' => 'Retained Earnings', 'type' => 'equity'],
            
            // Revenue (4000-4999)
            ['code' => '4100', 'name' => 'Product Sales', 'type' => 'revenue'],
            ['code' => '4200', 'name' => 'Service Income', 'type' => 'revenue'],
            
            // Expenses (5000-5999 Cost of Sales, 6000-9999 OpEx)
            ['code' => '5100', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '6100', 'name' => 'Salaries & Wages', 'type' => 'expense'],
            ['code' => '6200', 'name' => 'Rent Expense', 'type' => 'expense'],
            ['code' => '6300', 'name' => 'Marketing & Ads', 'type' => 'expense'],
        ];

        foreach ($coa as $item) {
            Account::updateOrCreate(['code' => $item['code']], $item);
        }

        // 2. Analytical Accounts (Cost Centers)
        $costCenters = [
            ['code' => 'CC-HQ', 'name' => 'Headquarters'],
            ['code' => 'CC-SALES', 'name' => 'Sales & Marketing'],
            ['code' => 'CC-DEV', 'name' => 'Engineering'],
        ];

        foreach ($costCenters as $cc) {
            AnalyticalAccount::updateOrCreate(['code' => $cc['code']], $cc);
        }

        $hq = AnalyticalAccount::where('code', 'CC-HQ')->first();
        $sales = AnalyticalAccount::where('code', 'CC-SALES')->first();

        // 3. Budgets
        $marketingAcc = Account::where('code', '6300')->first();
        Budget::updateOrCreate(
            ['account_uuid' => $marketingAcc->uuid],
            [
                'amount' => 50000,
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth()
            ]
        );

        // 4. Initial Journal Entries (Double Entry)
        $bank = Account::where('code' , '1010')->first();
        $revenue = Account::where('code', '4100')->first();
        $salary = Account::where('code', '6100')->first();
        $ap = Account::where('code', '2100')->first();

        // Entry 1: Sale of items ($10,000)
        $this->accounting->postEntry(
            ['entry_number' => 'INV-001', 'reference' => 'CUST-01', 'description' => 'Sales to Customer A'],
            [
                ['account_uuid' => $bank->uuid, 'debit' => 10000, 'credit' => 0, 'description' => 'Payment received'],
                ['account_uuid' => $revenue->uuid, 'debit' => 0, 'credit' => 10000, 'description' => 'Revenue recognized']
            ]
        );

        // Entry 2: Monthly Salaries ($6,000)
        $this->accounting->postEntry(
            ['entry_number' => 'PAY-001', 'description' => 'April Payroll'],
            [
                ['account_uuid' => $salary->uuid, 'debit' => 6000, 'credit' => 0, 'analytical_account_uuid' => $hq->uuid, 'description' => 'HQ Salaries'],
                ['account_uuid' => $bank->uuid, 'debit' => 0, 'credit' => 6000, 'description' => 'Bank payout']
            ]
        );

        // Entry 3: Marketing spend for AI variance demo
        for ($i = 0; $i < 5; $i++) {
            $date = now()->subDays(5 - $i)->toDateString();
            $this->accounting->postEntry(
                ['entry_number' => 'MKT-'.($i+1), 'date' => $date, 'description' => 'Ads Campaign Day ' . ($i+1)],
                [
                    ['account_uuid' => $marketingAcc->uuid, 'debit' => 2000, 'credit' => 0, 'analytical_account_uuid' => $sales->uuid],
                    ['account_uuid' => $bank->uuid, 'debit' => 0, 'credit' => 2000]
                ]
            );
        }
    }
}
