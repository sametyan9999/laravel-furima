<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    private function v(array $data)
    {
        $r = new RegisterRequest();
        return Validator::make($data, $r->rules(), method_exists($r,'messages') ? $r->messages() : []);
    }

    /** @test */ public function 名前_必須()
    {
        $res = $this->v([
            'name' => '',
            'email' => 'a@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertTrue($res->fails());
        $this->assertArrayHasKey('name', $res->errors()->toArray());
    }

    /** @test */ public function メール_必須_形式()
    {
        $bad = $this->v([
            'name' => '太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertTrue($bad->fails());
        $this->assertArrayHasKey('email', $bad->errors()->toArray());

        $bad2 = $this->v([
            'name' => '太郎',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertTrue($bad2->fails());
        $this->assertArrayHasKey('email', $bad2->errors()->toArray());
    }

    /** @test */ public function パスワード_必須_min8_確認一致_確認必須()
    {
        // 未入力
        $res1 = $this->v([
            'name' => '太郎',
            'email' => 'a@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $this->assertTrue($res1->fails());
        $this->assertArrayHasKey('password', $res1->errors()->toArray());

        // 7文字
        $res2 = $this->v([
            'name' => '太郎',
            'email' => 'a@example.com',
            'password' => 'short7',
            'password_confirmation' => 'short7',
        ]);
        $this->assertTrue($res2->fails());
        $this->assertArrayHasKey('password', $res2->errors()->toArray());

        // 不一致
        $res3 = $this->v([
            'name' => '太郎',
            'email' => 'a@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);
        $this->assertTrue($res3->fails());
        $this->assertArrayHasKey('password', $res3->errors()->toArray());

        // 確認未入力（confirmed が効く想定）
        $res4 = $this->v([
            'name' => '太郎',
            'email' => 'a@example.com',
            'password' => 'password123',
            'password_confirmation' => '',
        ]);
        $this->assertTrue($res4->fails());
        $this->assertArrayHasKey('password', $res4->errors()->toArray());
    }

    /** @test */ public function 正常系()
    {
        $ok = $this->v([
            'name' => '太郎',
            'email' => 'a@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertFalse($ok->fails());
    }
}
