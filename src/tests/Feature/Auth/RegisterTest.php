<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // バリデーションメッセージを日本語で検証
        config(['app.locale' => 'ja']);
        app()->setLocale('ja');
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    /** @test 名前が入力されていない場合、バリデーションメッセージが表示される */
    public function 名前未入力だとメッセージが表示される()
    {
        $res = $this->from('/register')->post('/register', $this->validPayload([
            'name' => '',
        ]));

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'name' => __('validation.required', ['attribute' => '名前']),
        ]);
        $this->assertGuest();
    }

    /** @test メールアドレスが入力されていない場合、バリデーションメッセージが表示される */
    public function メール未入力だとメッセージが表示される()
    {
        $res = $this->from('/register')->post('/register', $this->validPayload([
            'email' => '',
        ]));

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'email' => __('validation.required', ['attribute' => 'メールアドレス']),
        ]);
        $this->assertGuest();
    }

    /** @test パスワードが入力されていない場合、バリデーションメッセージが表示される */
    public function パスワード未入力だとメッセージが表示される()
    {
        $res = $this->from('/register')->post('/register', $this->validPayload([
            'password' => '',
            'password_confirmation' => '',
        ]));

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'password' => __('validation.required', ['attribute' => 'パスワード']),
        ]);
        $this->assertGuest();
    }

    /** @test パスワードが7文字以下の場合、バリデーションメッセージが表示される */
    public function パスワードが7文字以下だとメッセージが表示される()
    {
        $res = $this->from('/register')->post('/register', $this->validPayload([
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]));

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'password' => __('validation.min.string', ['attribute' => 'パスワード', 'min' => 8]),
        ]);
        $this->assertGuest();
    }

    /** @test パスワードが確認用と一致しない場合、バリデーションメッセージが表示される */
    public function パスワード不一致だとメッセージが表示される()
    {
        $res = $this->from('/register')->post('/register', $this->validPayload([
            'password_confirmation' => 'mismatch123',
        ]));

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'password' => __('validation.confirmed', ['attribute' => 'パスワード']),
        ]);
        $this->assertGuest();
    }

    /**
     * @test
     * 全ての項目が正しければ会員登録され、プロフィール設定画面に遷移する
     */
    public function 正常登録でプロフィール設定画面へリダイレクト()
    {
        $res = $this->post('/register', $this->validPayload());

        $res->assertRedirect(route('mypage.profile.edit'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}