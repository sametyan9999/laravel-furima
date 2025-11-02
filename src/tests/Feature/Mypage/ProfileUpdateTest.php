<?php

namespace Tests\Feature\Mypage;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
     * 期待挙動：プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧が正しく表示される
     */
    public function test_変更項目が初期値として過去設定されていること_プロフィール画像_ユーザー名_郵便番号_住所(): void
    {
        // ユーザー + プロフィール既存値
        $user = User::factory()->create(['name' => '初期ユーザー名']);
        $user->profile()->create([
            'avatar_path'   => '/storage/avatars/init.png',
            'postal_code'   => '123-4567',
            'address_line1' => '東京都テスト区1-2-3',
            'address_line2' => 'テストビル101',
            'phone'         => '0312345678',
        ]);

        // プロフィール編集画面で初期値がプレフィル表示されること
        $this->actingAs($user)
            ->get(route('mypage.profile.edit'))
            ->assertOk()
            ->assertSee('初期ユーザー名')
            ->assertSee('/storage/avatars/init.png')
            ->assertSee('123-4567')
            ->assertSee('東京都テスト区1-2-3')
            ->assertSee('テストビル101');
    }
}