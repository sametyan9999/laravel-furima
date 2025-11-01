<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    // 使っている認証パッケージに合わせて変更（Breeze/Fortifyなら /register がデフォルト）
    private const REGISTER_GET  = '/register';
    private const REGISTER_POST = '/register';

    /** @test */
    public function 登録ページが表示される(): void
    {
        $res = $this->get(self::REGISTER_GET);

        $res->assertOk();          // 200
        $res->assertSee('登録');   // ビュー文言が違う場合は削除可
    }

    /** @test */
    public function 名前未入力だとエラーになる(): void
    {
        $payload = [
            'name'                  => '',
            'email'                 => 'user@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->from(self::REGISTER_GET)->post(self::REGISTER_POST, $payload);

        $res->assertStatus(302)->assertSessionHasErrors(['name']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function メール未入力だとエラーになる(): void
    {
        $payload = [
            'name'                  => 'テスト太郎',
            'email'                 => '',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->from(self::REGISTER_GET)->post(self::REGISTER_POST, $payload);

        $res->assertStatus(302)->assertSessionHasErrors(['email']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function パスワード未入力だとエラーになる(): void
    {
        $payload = [
            'name'                  => 'テスト太郎',
            'email'                 => 'user@example.com',
            'password'              => '',
            'password_confirmation' => '',
        ];

        $res = $this->from(self::REGISTER_GET)->post(self::REGISTER_POST, $payload);

        $res->assertStatus(302)->assertSessionHasErrors(['password']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function パスワードが7文字以下だとエラーになる(): void
    {
        $payload = [
            'name'                  => 'テスト太郎',
            'email'                 => 'user@example.com',
            'password'              => 'short7', // 7文字
            'password_confirmation' => 'short7',
        ];

        $res = $this->from(self::REGISTER_GET)->post(self::REGISTER_POST, $payload);

        $res->assertStatus(302)->assertSessionHasErrors(['password']); // min:8 に該当
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function 確認用パスワードと不一致だとエラーになる(): void
    {
        $payload = [
            'name'                  => 'テスト太郎',
            'email'                 => 'user@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different123',
        ];

        $res = $this->from(self::REGISTER_GET)->post(self::REGISTER_POST, $payload);

        $res->assertStatus(302)->assertSessionHasErrors(['password']); // confirmed に該当
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function 既存メールアドレスは登録できない(): void
    {
        User::factory()->create([
            'name'     => '既存',
            'email'    => 'dup@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'name'                  => 'テスト太郎',
            'email'                 => 'dup@example.com', // 重複
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->from(self::REGISTER_GET)->post(self::REGISTER_POST, $payload);

        $res->assertStatus(302)->assertSessionHasErrors(['email']); // unique:users,email を想定
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1); // 既存のみ
    }

    /** @test */
    public function すべて正しく入力すると登録されログイン状態になる(): void
    {
        $payload = [
            'name'                  => 'テスト太郎',
            'email'                 => 'user@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->post(self::REGISTER_POST, $payload);

        // 1件作成されていること
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'name'  => 'テスト太郎',
        ]);

        // 認証済みになっていること
        $user = User::first();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('password123', $user->password));

        // リダイレクト（行き先は実装依存なので 302 のみ緩めに検証）
        $res->assertStatus(302);
    }
}
