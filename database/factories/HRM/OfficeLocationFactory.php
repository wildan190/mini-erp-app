<?php

namespace Database\Factories\HRM;

use App\Domain\HRM\Models\OfficeLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\HRM\Models\OfficeLocation>
 */
class OfficeLocationFactory extends Factory
{
    protected $model = OfficeLocation::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Office',
            'address' => $this->faker->address,
            'latitude' => $this->faker->latitude(-6.4, -6.0), // Jakarta area
            'longitude' => $this->faker->longitude(106.7, 106.9), // Jakarta area
            'radius' => $this->faker->numberBetween(50, 200),
            'is_active' => true,
        ];
    }
}
