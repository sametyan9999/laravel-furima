<?php

namespace Tests\Feature\Mypage;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MypageShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     * 期待挙動：プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧が正しく表示される
     */
    public function test_必要な情報が取得できる_プロフィール画像_ユーザー名_出品した商品一覧_購入した商品一覧が正しく表示される(): void
    {
        // ユーザー作成（プロフィール付き）
        $user = User::factory()->create(['name' => 'マイページユーザー']);
        $user->profile()->create([
            'avatar_path'   => '/storage/avatars/test.png',
            'postal_code'   => '123-4567',
            'address_line1' => '東京都テスト区1-2-3',
            'address_line2' => 'テストビル101',
            'phone'         => '0312345678',
        ]);

        // コンディション
        $condition = Condition::factory()->create(['name' => '新品']);

        // 出品した商品
        $sellingItem = Item::factory()->create([
            'user_id'      => $user->id,
            'condition_id' => $condition->id,
            'status'       => 'on_sale',
            'name'         => '出品テスト商品',
            'price'        => 3000,
            'image'        => 'items/sell.png',
        ]);

        // 購入した商品（別のユーザーの商品を購入）
        $seller = User::factory()->create();
        $buyItem = Item::factory()->create([
            'user_id'      => $seller->id,
            'condition_id' => $condition->id,
            'status'       => 'on_sale',
            'name'         => '購入テスト商品',
            'price'        => 4000,
            'image'        => 'items/buy.png',
        ]);

        // 購入処理
        $this->actingAs($user)
            ->post(route('purchase.store', $buyItem), ['payment_method' => 'convenience'])
            ->assertStatus(302);

        // --- 検証 ---

        // 1️⃣ プロフィール情報が正しく表示される（ユーザー名・画像）
        $this->actingAs($user)
            ->get(route('mypage.index'))
            ->assertOk()
            ->assertSee('マイページユーザー')
            ->assertSee('/storage/avatars/test.png');

        // 2️⃣ 出品した商品一覧に自分の出品が表示されている
        $this->get(route('mypage.index', ['view' => 'sell']))
            ->assertOk()
            ->assertSee('出品テスト商品');

        // 3️⃣ 購入した商品一覧に購入済み商品が表示されている
        $this->get(route('mypage.index', ['view' => 'buy']))
            ->assertOk()
            ->assertSee('購入テスト商品');
    }
}