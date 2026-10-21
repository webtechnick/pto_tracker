<?php

namespace Database\Factories;

use App\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'date' => fake()->dateTimeThisMonth(),
        ];
    }
}
