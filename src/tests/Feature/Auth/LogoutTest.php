<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * ログアウトができる
     */
    public function ログアウトができる()
    {
        $user = User::factory()->create();

        // ログイン状態にする
        $this->actingAs($user);

        // Fortify の /logout は POST
        $response = $this->post('/logout');

        // トップへリダイレクトし、未認証になる
        $response->assertStatus(302)->assertRedirect('/');
        $this->assertGuest();
    }
}