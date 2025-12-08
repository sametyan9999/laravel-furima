<?php

namespace Database\Factories;

use App\Models\TradeReview;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeReviewFactory extends Factory
{
    protected $model = TradeReview::class;

    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'reviewer_id' => User::factory(),
            'target_id'   => User::factory(),
            'score'       => $this->faker->numberBetween(1, 5),
            'comment'     => $this->faker->optional()->sentence,
        ];
    }
}