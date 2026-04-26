<?php

namespace Tests\Unit;

use App\Services\AI\FinanceAIService;
use PHPUnit\Framework\TestCase;

class FinanceAIServiceTest extends TestCase
{
    protected $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new FinanceAIService();
    }

    public function test_linear_regression_calculation()
    {
        // Simple linear data: y = 2x + 1
        $data = [
            0 => 1,
            1 => 3,
            2 => 5,
            3 => 7,
            4 => 9
        ];

        $result = $this->aiService->linearRegression($data);

        $this->assertEquals(2.0, $result['slope']);
        $this->assertEquals(1.0, $result['intercept']);
        $this->assertEquals(1.0, $result['r_squared']);
    }

    public function test_knn_classification()
    {
        $trainingSet = [
            ['features' => [1, 1], 'label' => 'A'],
            ['features' => [1, 2], 'label' => 'A'],
            ['features' => [5, 5], 'label' => 'B'],
            ['features' => [5, 6], 'label' => 'B'],
        ];

        // Item close to A group
        $predictionA = $this->aiService->knn($trainingSet, [1.5, 1.5], 3);
        $this->assertEquals('A', $predictionA);

        // Item close to B group
        $predictionB = $this->aiService->knn($trainingSet, [4.5, 5.5], 3);
        $this->assertEquals('B', $predictionB);
    }
}
