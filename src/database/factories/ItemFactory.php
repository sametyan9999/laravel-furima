<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'condition_id'   => Condition::factory(),
            'name'           => $this->faker->words(2, true),
            'description'    => $this->faker->sentence(),
            'brand'          => $this->faker->company(),
            'image'          => '/storage/items/sample.jpg',
            'price'          => $this->faker->numberBetween(100, 20000),
            'status'         => 'on_sale',
            'likes_count'    => 0,
            'comments_count' => 0,
            'sold_at'        => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Item $item) {
            // ランダムに既存カテゴリを1件紐付け（なければ作成）
            $categoryIds = Category::inRandomOrder()->limit(1)->pluck('id');
            if ($categoryIds->isEmpty()) {
                $categoryIds = collect([Category::factory()->create()->id]);
            }

            $item->categories()->sync($categoryIds);
        });
    }

    /** 売却済み（sold）状態 */
    public function sold(): self
    {
        return $this->state(fn () => [
            'status'  => 'sold',
            'sold_at' => now(),
        ]);
    }

    /** 下書き（draft）状態 */
    public function draft(): self
    {
        return $this->state(fn () => [
            'status' => 'draft',
        ]);
    }
}