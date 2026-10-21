<?php

namespace Database\Factories;

use App\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'title' => fake()->sentence(),
            'color' => fake()->colorName(),
            'bgcolor' => fake()->colorName(),
            'is_on_call' => 0,
            'manager_id' => null,
        ];
    }
}
