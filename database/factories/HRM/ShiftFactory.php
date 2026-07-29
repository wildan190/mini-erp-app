<?php

namespace Database\Factories\HRM;

use App\Domain\HRM\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\HRM\Models\Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $index = 0;
        $names = ['Morning Shift', 'Day Shift', 'Night Shift', 'Evening Shift', 'General Shift', 'Split Shift', 'Weekend Shift', 'Holiday Shift'];
        $suffix = uniqid('', true);

        return [
            'name'       => ($names[$index++ % count($names)] ?? 'Shift') . ' ' . substr($suffix, -4),
            'start_time' => '09:00:00',
            'end_time'   => '17:00:00',
        ];
    }
}
