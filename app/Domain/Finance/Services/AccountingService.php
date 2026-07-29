<?php

namespace App\Domain\Finance\Services;

use App\Domain\Finance\Models\Account;
use App\Domain\Finance\Models\JournalEntry;
use App\Domain\Finance\Models\JournalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountingService
{
    /**
     * Post a balanced journal entry
     */
    public function postEntry(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $totalDebit = collect($items)->sum('debit');
            $totalCredit = collect($items)->sum('credit');

            if (abs($totalDebit - $totalCredit) > 0.001) {
                throw new \Exception("Journal entry is not balanced. Debits: $totalDebit, Credits: $totalCredit");
            }

            $entry = JournalEntry::create([
                'entry_number' => $data['entry_number'] ?? 'JV-' . strtoupper(Str::random(8)),
                'date' => $data['date'] ?? now()->toDateString(),
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => 'posted'
            ]);

            foreach ($items as $item) {
                JournalItem::create([
                    'entry_uuid' => $entry->uuid,
                    'account_uuid' => $item['account_uuid'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'balance' => ($item['debit'] ?? 0) - ($item['credit'] ?? 0),
                    'analytical_account_uuid' => $item['analytical_account_uuid'] ?? null,
                    'description' => $item['description'] ?? null
                ]);
            }

            return $entry;
        });
    }

    /**
     * Get Profit & Loss data
     */
    public function getProfitAndLoss($startDate, $endDate)
    {
        $records = Account::whereIn('type', ['revenue', 'expense'])
            ->with(['journalItems' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('entry', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                      ->where('status', 'posted');
                });
            }])->get();

        $report = [
            'revenue' => [],
            'expense' => [],
            'total_revenue' => 0,
            'total_expense' => 0,
            'net_profit' => 0
        ];

        foreach ($records as $account) {
            $balance = $account->journalItems->sum('balance');
            
            // For revenue, negative balance in items (credit) means income
            // But we'll use absolute logic for reporting
            $amount = abs($balance);

            if ($account->type === 'revenue') {
                $report['revenue'][] = ['name' => $account->name, 'amount' => $amount];
                $report['total_revenue'] += $amount;
            } else {
                $report['expense'][] = ['name' => $account->name, 'amount' => $amount];
                $report['total_expense'] += $amount;
            }
        }

        $report['net_profit'] = $report['total_revenue'] - $report['total_expense'];
        return $report;
    }

    /**
     * Get Balance Sheet data
     */
    public function getBalanceSheet($date)
    {
        $accounts = Account::whereIn('type', ['asset', 'liability', 'equity'])
            ->with(['journalItems' => function ($query) use ($date) {
                $query->whereHas('entry', function ($q) use ($date) {
                    $q->where('date', '<=', $date)
                      ->where('status', 'posted');
                });
            }])->get();

        $report = [
            'assets' => [],
            'liabilities' => [],
            'equity' => [],
            'total_assets' => 0,
            'total_liabilities' => 0,
            'total_equity' => 0
        ];

        foreach ($accounts as $account) {
            $balance = $account->journalItems->sum('balance');
            
            if ($account->type === 'asset') {
                $report['assets'][] = ['name' => $account->name, 'amount' => $balance];
                $report['total_assets'] += $balance;
            } elseif ($account->type === 'liability') {
                $report['liabilities'][] = ['name' => $account->name, 'amount' => abs($balance)];
                $report['total_liabilities'] += abs($balance);
            } else {
                $report['equity'][] = ['name' => $account->name, 'amount' => abs($balance)];
                $report['total_equity'] += abs($balance);
            }
        }

        return $report;
    }

    /**
     * Get Cash Flow Statement (Simplified Direct Method)
     */
    public function getCashFlowStatement($startDate, $endDate)
    {
        // Get movements in Cash/Bank accounts (Code 1010 in our seeder)
        $cashAccounts = Account::where('code', 'like', '10%')->pluck('uuid');

        $items = JournalItem::whereIn('account_uuid', $cashAccounts)
            ->whereHas('entry', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->where('status', 'posted');
            })
            ->with(['entry', 'account'])
            ->get();

        $report = [
            'operating_activities' => 0,
            'investing_activities' => 0,
            'financing_activities' => 0,
            'net_cash_flow' => 0,
            'details' => []
        ];

        foreach ($items as $item) {
            $amount = $item->debit - $item->credit;
            $report['net_cash_flow'] += $amount;
            
            // Simplified logic: all seeded is operating for now
            $report['operating_activities'] += $amount;
            
            $report['details'][] = [
                'date' => $item->entry->date,
                'description' => $item->description ?? $item->entry->description,
                'amount' => $amount
            ];
        }

        return $report;
    }
}
