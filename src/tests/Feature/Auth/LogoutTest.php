<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private const LOGOUT_POST = '/logout';
    private const HOME = '/'; // 遷移先が決まっていれば置き換え

    /** @test */
    public function ログイン中ならログアウトできる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $res = $this->post(self::LOGOUT_POST);

        $this->assertGuest();
        $res->assertStatus(302); // Fortify/Breeze 既定はリダイレクト
        // $res->assertRedirect(self::HOME);
    }

    /** @test */
    public function 未ログインでログアウト叩いてもゲストのまま()
    {
        $res = $this->post(self::LOGOUT_POST);

        $this->assertGuest();
        $res->assertStatus(302);
    }
}
