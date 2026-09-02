<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if already seeded (more than just the test row)
        if (DB::table('accounts')->whereNull('deleted_at')->count() > 5) {
            $this->command->info('Chart of Accounts already seeded, skipping.');
            return;
        }

        $now = now();

        // Helper to make a UUID
        $uuid = fn() => Str::uuid()->toString();

        // ── ASSETS ───────────────────────────────────────────────────────────
        $currentAssetsUuid    = $uuid();
        $fixedAssetsUuid      = $uuid();
        $cashUuid             = $uuid();
        $bankUuid             = $uuid();
        $arUuid               = $uuid();
        $inventoryUuid        = $uuid();
        $prepaidUuid          = $uuid();
        $equipmentUuid        = $uuid();
        $vehiclesUuid         = $uuid();
        $accDepreciationUuid  = $uuid();

        // ── LIABILITIES ───────────────────────────────────────────────────────
        $currentLiabUuid      = $uuid();
        $longTermLiabUuid     = $uuid();
        $apUuid               = $uuid();
        $taxPayableUuid       = $uuid();
        $salariesPayableUuid  = $uuid();
        $bankLoanUuid         = $uuid();

        // ── EQUITY ────────────────────────────────────────────────────────────
        $equityRootUuid       = $uuid();
        $paidInCapitalUuid    = $uuid();
        $retainedEarningsUuid = $uuid();
        $currentYearPnLUuid   = $uuid();

        // ── REVENUE ───────────────────────────────────────────────────────────
        $revenueRootUuid      = $uuid();
        $salesRevenueUuid     = $uuid();
        $serviceRevenueUuid   = $uuid();
        $otherRevenueUuid     = $uuid();

        // ── EXPENSES ──────────────────────────────────────────────────────────
        $expenseRootUuid      = $uuid();
        $cogsUuid             = $uuid();
        $salariesExpUuid      = $uuid();
        $rentUuid             = $uuid();
        $utilitiesUuid        = $uuid();
        $marketingUuid        = $uuid();
        $depreciationExpUuid  = $uuid();
        $taxExpenseUuid       = $uuid();
        $operatingExpUuid     = $uuid();
        $itExpUuid            = $uuid();
        $officeSuppliesUuid   = $uuid();
        $travelUuid           = $uuid();
        $projectExpUuid       = $uuid();

        $accounts = [
            // ── ASSETS ──────────────────────────────────────────────────────
            ['uuid' => $currentAssetsUuid,    'code' => '1000', 'name' => 'Current Assets',                'type' => 'asset',     'parent_uuid' => null,               'is_reconcilable' => false],
            ['uuid' => $cashUuid,             'code' => '1001', 'name' => 'Cash on Hand',                  'type' => 'asset',     'parent_uuid' => $currentAssetsUuid,  'is_reconcilable' => true],
            ['uuid' => $bankUuid,             'code' => '1002', 'name' => 'Bank Accounts',                 'type' => 'asset',     'parent_uuid' => $currentAssetsUuid,  'is_reconcilable' => true],
            ['uuid' => $arUuid,               'code' => '1100', 'name' => 'Accounts Receivable',           'type' => 'asset',     'parent_uuid' => $currentAssetsUuid,  'is_reconcilable' => true],
            ['uuid' => $inventoryUuid,        'code' => '1200', 'name' => 'Inventory',                     'type' => 'asset',     'parent_uuid' => $currentAssetsUuid,  'is_reconcilable' => false],
            ['uuid' => $prepaidUuid,          'code' => '1300', 'name' => 'Prepaid Expenses',              'type' => 'asset',     'parent_uuid' => $currentAssetsUuid,  'is_reconcilable' => false],
            ['uuid' => $fixedAssetsUuid,      'code' => '1500', 'name' => 'Fixed Assets',                  'type' => 'asset',     'parent_uuid' => null,               'is_reconcilable' => false],
            ['uuid' => $equipmentUuid,        'code' => '1510', 'name' => 'Equipment & Machinery',         'type' => 'asset',     'parent_uuid' => $fixedAssetsUuid,   'is_reconcilable' => false],
            ['uuid' => $vehiclesUuid,         'code' => '1520', 'name' => 'Vehicles',                      'type' => 'asset',     'parent_uuid' => $fixedAssetsUuid,   'is_reconcilable' => false],
            ['uuid' => $accDepreciationUuid,  'code' => '1590', 'name' => 'Accumulated Depreciation',      'type' => 'asset',     'parent_uuid' => $fixedAssetsUuid,   'is_reconcilable' => false],

            // ── LIABILITIES ──────────────────────────────────────────────────
            ['uuid' => $currentLiabUuid,      'code' => '2000', 'name' => 'Current Liabilities',           'type' => 'liability', 'parent_uuid' => null,               'is_reconcilable' => false],
            ['uuid' => $apUuid,               'code' => '2001', 'name' => 'Accounts Payable',              'type' => 'liability', 'parent_uuid' => $currentLiabUuid,   'is_reconcilable' => true],
            ['uuid' => $taxPayableUuid,       'code' => '2100', 'name' => 'Tax Payable (PPN/PPh)',          'type' => 'liability', 'parent_uuid' => $currentLiabUuid,   'is_reconcilable' => false],
            ['uuid' => $salariesPayableUuid,  'code' => '2200', 'name' => 'Salaries Payable',              'type' => 'liability', 'parent_uuid' => $currentLiabUuid,   'is_reconcilable' => false],
            ['uuid' => $longTermLiabUuid,     'code' => '2500', 'name' => 'Long-Term Liabilities',         'type' => 'liability', 'parent_uuid' => null,               'is_reconcilable' => false],
            ['uuid' => $bankLoanUuid,         'code' => '2510', 'name' => 'Bank Loans',                    'type' => 'liability', 'parent_uuid' => $longTermLiabUuid,  'is_reconcilable' => true],

            // ── EQUITY ───────────────────────────────────────────────────────
            ['uuid' => $equityRootUuid,       'code' => '3000', 'name' => 'Equity',                        'type' => 'equity',    'parent_uuid' => null,               'is_reconcilable' => false],
            ['uuid' => $paidInCapitalUuid,    'code' => '3100', 'name' => 'Paid-In Capital',               'type' => 'equity',    'parent_uuid' => $equityRootUuid,    'is_reconcilable' => false],
            ['uuid' => $retainedEarningsUuid, 'code' => '3200', 'name' => 'Retained Earnings',             'type' => 'equity',    'parent_uuid' => $equityRootUuid,    'is_reconcilable' => false],
            ['uuid' => $currentYearPnLUuid,   'code' => '3300', 'name' => 'Current Year Profit & Loss',    'type' => 'equity',    'parent_uuid' => $equityRootUuid,    'is_reconcilable' => false],

            // ── REVENUE ──────────────────────────────────────────────────────
            ['uuid' => $revenueRootUuid,      'code' => '4000', 'name' => 'Revenue',                       'type' => 'revenue',   'parent_uuid' => null,               'is_reconcilable' => false],
            ['uuid' => $salesRevenueUuid,     'code' => '4100', 'name' => 'Sales Revenue',                 'type' => 'revenue',   'parent_uuid' => $revenueRootUuid,   'is_reconcilable' => true],
            ['uuid' => $serviceRevenueUuid,   'code' => '4200', 'name' => 'Service Revenue',               'type' => 'revenue',   'parent_uuid' => $revenueRootUuid,   'is_reconcilable' => true],
            ['uuid' => $otherRevenueUuid,     'code' => '4900', 'name' => 'Other Revenue',                 'type' => 'revenue',   'parent_uuid' => $revenueRootUuid,   'is_reconcilable' => false],

            // ── EXPENSES ─────────────────────────────────────────────────────
            ['uuid' => $expenseRootUuid,      'code' => '5000', 'name' => 'Expenses',                      'type' => 'expense',   'parent_uuid' => null,               'is_reconcilable' => false],
            ['uuid' => $cogsUuid,             'code' => '5100', 'name' => 'Cost of Goods Sold (COGS)',      'type' => 'expense',   'parent_uuid' => $expenseRootUuid,   'is_reconcilable' => false],
            ['uuid' => $operatingExpUuid,     'code' => '5200', 'name' => 'Operating Expenses',            'type' => 'expense',   'parent_uuid' => $expenseRootUuid,   'is_reconcilable' => false],
            ['uuid' => $salariesExpUuid,      'code' => '5210', 'name' => 'Salaries & Wages',              'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $rentUuid,             'code' => '5220', 'name' => 'Rent & Office Space',           'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $utilitiesUuid,        'code' => '5230', 'name' => 'Utilities (Electric, Water)',   'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $marketingUuid,        'code' => '5240', 'name' => 'Marketing & Advertising',       'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $itExpUuid,            'code' => '5250', 'name' => 'IT & Software Subscriptions',   'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $officeSuppliesUuid,   'code' => '5260', 'name' => 'Office Supplies',               'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $travelUuid,           'code' => '5270', 'name' => 'Travel & Transportation',       'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $projectExpUuid,       'code' => '5280', 'name' => 'Project Expenses',              'type' => 'expense',   'parent_uuid' => $operatingExpUuid,  'is_reconcilable' => false],
            ['uuid' => $depreciationExpUuid,  'code' => '5300', 'name' => 'Depreciation Expense',          'type' => 'expense',   'parent_uuid' => $expenseRootUuid,   'is_reconcilable' => false],
            ['uuid' => $taxExpenseUuid,       'code' => '5400', 'name' => 'Income Tax Expense',            'type' => 'expense',   'parent_uuid' => $expenseRootUuid,   'is_reconcilable' => false],
        ];

        foreach ($accounts as $account) {
            // Skip if code already exists (e.g. the original "1001 Payable" row)
            $exists = DB::table('accounts')
                ->where('code', $account['code'])
                ->whereNull('deleted_at')
                ->exists();

            if (!$exists) {
                DB::table('accounts')->insert(array_merge($account, [
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]));
            }
        }

        $this->command->info('Chart of Accounts seeded successfully (' . count($accounts) . ' accounts).');
    }
}
