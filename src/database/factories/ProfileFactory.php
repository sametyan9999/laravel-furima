<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 必須最小のみ。テーブル側でtimestamps無しならここも不要項目は追加しない
        return [
            // 必要になった時にだけ項目を足す（YAGNI）
        ];
    }
}