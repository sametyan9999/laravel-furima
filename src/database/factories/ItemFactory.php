<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'category_id'   => Category::factory(),   // ✅ カテゴリを関連付け
            'condition_id'  => Condition::factory(),  // ✅ 状態を関連付け
            'name'          => $this->faker->words(2, true),
            'description'   => $this->faker->sentence(),
            'brand'         => $this->faker->company(),
            'image'         => 'items/sample.jpg',
            'price'         => $this->faker->numberBetween(300, 20000),
            'status'        => 'active',
            'likes_count'   => 0,
            'comments_count'=> 0,
            'sold_at'       => null,
        ];
    }
}
