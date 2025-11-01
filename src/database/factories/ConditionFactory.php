<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Condition;

class ConditionFactory extends Factory
{
    protected $model = Condition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['新品', '中古', 'ほぼ新品', '傷や汚れあり']),
        ];
    }
}
