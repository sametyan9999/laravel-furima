<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test メール未入力だとバリデーションメッセージが表示される */
    public function メール未入力だとバリデーションメッセージが表示される(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response = $this->get('/login');
        $response->assertSee('メールアドレスを入力してください');
    }

    /** @test パスワード未入力だとバリデーションメッセージが表示される */
    public function パスワード未入力だとバリデーションメッセージが表示される(): void
    {
        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response = $this->get('/login');
        $response->assertSee('パスワードを入力してください');
    }

    /** @test 誤った情報の場合バリデーションメッセージが表示される */
    public function 誤った情報の場合バリデーションメッセージが表示される(): void
    {
        // 正しいユーザーはいるが、誤ったパスワードで試行
        User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('correct-pass'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertStatus(302);
        $response = $this->get('/login');
        // 要件票どおり（resources/lang/ja/auth.php の 'failed'）
        $response->assertSee('ログイン情報が登録されていません');
    }

    /** @test 正しい情報が入力された場合ログインできる */
    public function 正しい情報が入力された場合ログインできる(): void
    {
        $user = User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticatedAs($user);
    }
}