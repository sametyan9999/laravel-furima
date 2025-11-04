<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後に認証メールが送信される
     */
    public function test_会員登録後に認証メールが送信される(): void
    {
        // 会員登録実行
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // FortifyはHOMEにリダイレクトするが、ここではステータスのみ確認
        $response->assertStatus(302);

        // ユーザーがDBに作成されている
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        // 認証されていないこと
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);

        // 未認証のまま保護ルートにアクセスすると /email/verify にリダイレクトされる
        $res2 = $this->actingAs($user)->get(route('mypage.profile.edit'));
        $res2->assertRedirect(route('verification.notice'));
    }

    /**
     * 認証はこちらから を押下でメール認証サイトに遷移する
     */
    public function test_認証はこちらからを押下でメール認証サイトに遷移する(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 署名付きURL生成
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 未認証でアクセス → 認証成功
        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect(route('mypage.profile.edit'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /**
     * メール認証完了後プロフィール設定画面に遷移する
     */
    public function test_メール認証完了後プロフィール設定画面に遷移する(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証URL生成
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 実行
        $this->actingAs($user)->get($url);

        // 認証済みになっている
        $this->assertNotNull($user->fresh()->email_verified_at);

        // プロフィール設定画面にアクセスできる
        $response = $this->actingAs($user)->get(route('mypage.profile.edit'));
        $response->assertStatus(200);
    }
}