<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const REQUEST_GET  = '/forgot-password';
    private const REQUEST_POST = '/forgot-password';
    // ビューを作らない方針なので RESET_GET は使わない
    // private const RESET_GET    = '/reset-password/{token}';
    private const RESET_POST   = '/reset-password';

    /** @test */
    public function 再設定メールを要求できる()
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);

        $res = $this->from(self::REQUEST_GET)->post(self::REQUEST_POST, [
            'email' => 'user@example.com',
        ]);

        // 画面遷移だけ確認（Fortify は 302 リダイレクト）
        $res->assertStatus(302);

        // 通知（ResetPassword）が送られていること
        Notification::assertSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function トークンでパスワードを再設定できる()
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);

        // 1) トークン発行をトリガー
        $this->post(self::REQUEST_POST, ['email' => $user->email])->assertStatus(302);

        // 2) 送られた通知からトークンを取り出し、そのまま POST で再設定を検証
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {

            // ★ ビュー検証（GET /reset-password/{token}）は実施しない

            // 3) パスワード再設定
            $res = $this->post(self::RESET_POST, [
                'token'    => $notification->token,
                'email'    => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]);

            // Fortify は成功時 302
            $res->assertStatus(302);

            // DB のパスワードが更新されていること
            $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));

            return true;
        });
    }
}
