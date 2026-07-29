<?php

namespace Database\Seeders;

use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\Finance\Models\InventoryMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FinanceDataSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = Carbon::now()->subMonths(24)->startOfMonth();
        $currentDate = Carbon::now();

        $baseRevenue = 50000;
        $growthRate = 1.02; // 2% monthly growth

        while ($startDate <= $currentDate) {
            // Generate Revenue for the month
            $monthlyRevenue = $baseRevenue * pow($growthRate, $startDate->diffInMonths(Carbon::now()->subMonths(24)));
            $monthlyRevenue += rand(-2000, 2000); // Add some noise

            FinancialRecord::create([
                'type' => 'revenue',
                'category' => 'Product Sales',
                'amount' => $monthlyRevenue,
                'record_date' => $startDate->copy()->day(5),
                'description' => 'Monthly sales revenue for ' . $startDate->format('M Y'),
            ]);

            // Generate Expenses (around 70% of revenue)
            $monthlyExpense = $monthlyRevenue * 0.7;
            $monthlyExpense += rand(-1000, 1000);

            FinancialRecord::create([
                'type' => 'expense',
                'category' => 'Operational Cost',
                'amount' => $monthlyExpense,
                'record_date' => $startDate->copy()->day(20),
                'description' => 'Monthly operational expenses for ' . $startDate->format('M Y'),
            ]);

            // Inventory Movements (Sample for KNN)
            $productUuid = Str::uuid();
            $stockLevel = 1000;
            
            for ($i = 0; $i < 4; $i++) {
                $outQty = rand(50, 200);
                $stockLevel -= $outQty;
                
                InventoryMovement::create([
                    'product_uuid' => $productUuid,
                    'quantity' => $outQty,
                    'uom' => 'Units',
                    'type' => 'out',
                    'stock_level_after' => $stockLevel,
                    'recorded_at' => $startDate->copy()->addDays(rand(1, 28)),
                ]);

                if ($stockLevel < 300) {
                    $inQty = 800;
                    $stockLevel += $inQty;
                    InventoryMovement::create([
                        'product_uuid' => $productUuid,
                        'quantity' => $inQty,
                        'uom' => 'Units',
                        'type' => 'in',
                        'stock_level_after' => $stockLevel,
                        'recorded_at' => $startDate->copy()->addDays(rand(1, 28)),
                    ]);
                }
            }

            $startDate->addMonth();
        }
    }
}
