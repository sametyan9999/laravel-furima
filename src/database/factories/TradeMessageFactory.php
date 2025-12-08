<?php

namespace Database\Factories;

use App\Models\TradeMessage;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeMessageFactory extends Factory
{
    protected $model = TradeMessage::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'purchase_id' => Purchase::factory(),
            'body'        => $this->faker->sentence,
            'image_path'  => null,
            'is_deleted'  => false,
        ];
    }
}