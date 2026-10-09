<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => 'Casual Leave ' . fake()->unique()->numberBetween(1, 1000),
            'code' => 'CASUAL_' . fake()->unique()->numberBetween(1, 1000),
            'max_days_per_year' => 12,
            'description' => 'Casual leave allowance',
        ];
    }
}
