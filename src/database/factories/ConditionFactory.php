<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Condition;

class ConditionFactory extends Factory
{
    protected $model = Condition::class;

    public function definition(): array
    {
        static $used = [];

        $candidates = ['新品', '中古', 'ほぼ新品', '傷や汚れあり'];
        $available = array_values(array_diff($candidates, $used));

        if (empty($available)) {
            // 使い切ったらランダムで再利用（重複エラー防止）
            return ['name' => $this->faker->unique()->word()];
        }

        $name = $this->faker->randomElement($available);
        $used[] = $name;

        return ['name' => $name];
    }
}