<?php
declare(strict_types=1);

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

    /** 状態の定数（マジック文字列回避） */
    private const STATUS_ON_SALE = 'on_sale';
    private const STATUS_SOLD    = 'sold';
    private const STATUS_DRAFT   = 'draft';

    public function definition(): array
    {
        // 既存カテゴリが無ければ1件作る
        $categoryId = Category::query()->inRandomOrder()->value('id')
            ?? Category::factory()->create()->id;

        return [
            'user_id'        => User::factory(),
            'condition_id'   => Condition::factory(),
            'category_id'    => $categoryId,               // 代表カテゴリ（NOT NULL対策）
            'name'           => $this->faker->words(2, true),
            'description'    => $this->faker->sentence(),
            'brand'          => $this->faker->company(),
            'image'          => '/storage/items/sample.jpg',
            'price'          => $this->faker->numberBetween(100, 20000),
            'status'         => self::STATUS_ON_SALE,
            'likes_count'    => 0,
            'comments_count' => 0,
            'sold_at'        => null,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (Item $item): void {
            // 代表カテゴリを含む最小1件を必ず紐付け
            $id = $item->category_id ?? Category::factory()->create()->id;
            $item->categories()->sync([$id]);
        });
    }

    /** 売却済み（sold）状態 */
    public function sold(): self
    {
        return $this->state(fn (): array => [
            'status'  => self::STATUS_SOLD,
            'sold_at' => now(),
        ]);
    }

    /** 下書き（draft）状態 */
    public function draft(): self
    {
        return $this->state(fn (): array => [
            'status' => self::STATUS_DRAFT,
        ]);
    }
}