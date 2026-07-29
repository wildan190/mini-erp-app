<?php

namespace App\Services\AI;

use App\Domain\Finance\Models\Account;
use App\Domain\Finance\Models\Budget;
use App\Domain\Finance\Models\JournalItem;
use Illuminate\Support\Facades\DB;

class FinanceAIService
{
    /**
     * AI-Driven Budget Variance Forecasting (Linear Regression)
     * Predicts if a department will exceed budget based on current spend trends.
     */
    public function predictBudgetVariance(string $accountUuid)
    {
        $budget = Budget::where('account_uuid', $accountUuid)
            ->where('end_date', '>=', now())
            ->first();

        if (!$budget) return null;

        // Get daily spending for this month
        $spending = JournalItem::where('account_uuid', $accountUuid)
            ->join('journal_entries', 'journal_items.entry_uuid', '=', 'journal_entries.uuid')
            ->whereBetween('journal_entries.date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
            ->select('journal_entries.date', DB::raw('SUM(journal_items.debit - journal_items.credit) as amount'))
            ->groupBy('journal_entries.date')
            ->orderBy('journal_entries.date')
            ->get();

        if ($spending->count() < 3) return ['status' => 'insufficient_data'];

        $data = [];
        $cumulative = 0;
        foreach ($spending as $i => $s) {
            $cumulative += (float)$s->amount;
            $data[$i] = $cumulative;
        }

        $model = $this->linearRegression($data);
        
        // Predict for the end of month (30 days)
        $predictedEndOfMonthSpend = ($model['slope'] * 30) + $model['intercept'];
        
        return [
            'budget_amount' => (float)$budget->amount,
            'current_spend' => $cumulative,
            'predicted_month_end' => $predictedEndOfMonthSpend,
            'variance_probability' => $predictedEndOfMonthSpend > $budget->amount ? 'High' : 'Low',
            'confidence_score' => $model['r_squared']
        ];
    }

    /**
     * Transaction Auto-Categorization (KNN)
     * Suggests a GL Account based on transaction description.
     */
    public function suggestAccount(string $description)
    {
        // Get historical items with descriptions
        $history = JournalItem::whereNotNull('description')
            ->whereHas('entry', function($q) { $q->where('status', 'posted'); })
            ->with('account')
            ->limit(100)
            ->get();

        if ($history->isEmpty()) return null;

        $trainingSet = $history->map(function ($item) {
            return [
                'features' => $this->textToVector($item->description),
                'label' => $item->account_uuid,
                'account_name' => $item->account->name
            ];
        })->toArray();

        $targetFeatures = $this->textToVector($description);
        $suggestedUuid = $this->knn($trainingSet, $targetFeatures, 3);
        
        $account = Account::find($suggestedUuid);
        return [
            'account_uuid' => $suggestedUuid,
            'account_name' => $account->name ?? 'Unknown',
            'confidence' => 'Moderate (History Match)'
        ];
    }

    /**
     * Simple Linear Regression: y = mx + c
     */
    public function linearRegression(array $data)
    {
        $n = count($data);
        if ($n === 0) return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];

        $x = array_keys($data);
        $y = array_values($data);

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXX = 0;
        $sumXY = 0;

        foreach ($x as $i => $val) {
            $sumXX += $val * $val;
            $sumXY += $val * $y[$i];
        }

        $denominator = ($n * $sumXX) - ($sumX * $sumX);
        if ($denominator == 0) return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;

        // R-Squared calculation
        $yMean = $sumY / $n;
        $ssTot = 0;
        $ssRes = 0;
        foreach ($y as $i => $val) {
            $fi = $slope * $x[$i] + $intercept;
            $ssTot += ($val - $yMean) ** 2;
            $ssRes += ($val - $fi) ** 2;
        }

        $rSquared = $ssTot == 0 ? 0 : 1 - ($ssRes / $ssTot);

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => $rSquared
        ];
    }

    /**
     * Predict value using linear regression formula: y = mx + c
     */
    public function predictLinear($x, $slope, $intercept)
    {
        return ($slope * $x) + $intercept;
    }

    /**
     * Multi-period forecast based on linear regression
     */
    public function forecast(array $data, int $periods = 6)
    {
        $model = $this->linearRegression($data);
        $n = count($data);
        $results = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $results[] = [
                'period' => $n + $i + 1,
                'value' => $this->predictLinear($n + $i, $model['slope'], $model['intercept'])
            ];
        }
        
        return $results;
    }

    /**
     * K-Nearest Neighbors Algorithm
     */
    public function knn(array $samples, array $target, int $k = 3)
    {
        $distances = [];
        foreach ($samples as $index => $sample) {
            $dist = $this->euclideanDistance($sample['features'], $target);
            $distances[] = ['dist' => $dist, 'label' => $sample['label']];
        }

        usort($distances, fn($a, $b) => $a['dist'] <=> $b['dist']);
        
        $nearest = array_slice($distances, 0, $k);
        $counts = array_count_values(array_column($nearest, 'label'));
        arsort($counts);

        return array_key_first($counts);
    }

    private function euclideanDistance(array $a, array $b)
    {
        $sum = 0;
        $length = max(count($a), count($b));
        for ($i = 0; $i < $length; $i++) {
            $sum += (($a[$i] ?? 0) - ($b[$i] ?? 0)) ** 2;
        }
        return sqrt($sum);
    }

    /**
     * Primitive Vectorization for text
     */
    private function textToVector(string $text)
    {
        $words = str_word_count(strtolower($text), 1);
        $vector = array_fill(0, 10, 0); // Static 10-dim vector for demo
        foreach ($words as $i => $word) {
            if ($i < 10) $vector[$i] = strlen($word);
        }
        return $vector;
    }
}
