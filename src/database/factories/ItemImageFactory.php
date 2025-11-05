<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 使っていない場合もYAGNIに反しない最小スケルトンを維持
        return [];
    }
}