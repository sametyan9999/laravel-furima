<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // ルートは環境に合わせて必要なら変更
    private const LOGIN_GET  = '/login';
    private const LOGIN_POST = '/login';
    private const HOME       = '/'; // 成功後の遷移先が決まっていれば route('dashboard') 等に変更

    /** @test */
    public function ログインページが表示される()
    {
        $res = $this->get(self::LOGIN_GET);
        $res->assertOk();
        $res->assertSee('ログイン'); // ビュー文言に依存するなら削除OK
    }

    /** @test */
    public function 正しい情報でログインできる()
    {
        $user = User::factory()->create([
            'email'    => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->post(self::LOGIN_POST, [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $res->assertStatus(302);
        // 具体の遷移先が決まっているなら assertRedirect(self::HOME);
    }

    /** @test */
    public function 誤った情報ではログインできない()
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->from(self::LOGIN_GET)->post(self::LOGIN_POST, [
            'email'    => 'user@example.com',
            'password' => 'wrong-pass',
        ]);

        $this->assertGuest();
        $res->assertStatus(302);                // バリデーションエラーで戻る
        $res->assertSessionHasErrors('email');  // Fortify/Breeze 既定のキー
    }

    /** @test */
    public function メール未入力だとエラーになる()
    {
        $res = $this->from(self::LOGIN_GET)->post(self::LOGIN_POST, [
            'email'    => '',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $res->assertStatus(302);
        $res->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワード未入力だとエラーになる()
    {
        $res = $this->from(self::LOGIN_GET)->post(self::LOGIN_POST, [
            'email'    => 'user@example.com',
            'password' => '',
        ]);

        $this->assertGuest();
        $res->assertStatus(302);
        $res->assertSessionHasErrors('password');
    }
}
