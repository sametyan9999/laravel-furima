<?php

namespace Tests\Feature\Items;

use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemCommentTest extends TestCase
{
    use RefreshDatabase;

    private function makeItem(): Item
    {
        $condition = Condition::factory()->create(['name' => '新品']);
        return Item::factory()->create([
            'condition_id' => $condition->id,
            'comments_count' => 0,
        ]);
    }

    /**
     * ログイン済みのユーザーはコメントを送信できる
     */
    public function test_ログイン済みのユーザーはコメントを送信できる(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem();

        $this->actingAs($user)
            ->from(route('items.show', $item))
            ->post(route('items.comments.store', $item), [
                'body' => 'テストコメント本文',
            ])
            ->assertRedirect(route('items.show', $item));

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'body'    => 'テストコメント本文',
        ]);

        $this->assertEquals(1, $item->fresh()->comments_count);

        $res = $this->get(route('items.show', $item));
        $res->assertOk();
        $res->assertSee('コメント（1）');
        $res->assertSee('テストコメント本文');
    }

    /**
     * ログイン前のユーザーはコメントを送信できない
     */
    public function test_ログイン前のユーザーはコメントを送信できない(): void
    {
        $item = $this->makeItem();

        $this->post(route('items.comments.store', $item), [
            'body' => 'ゲストのコメント',
        ])->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'body'    => 'ゲストのコメント',
        ]);
    }

    /**
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_コメントが入力されていない場合_バリデーションメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem();

        $response = $this->actingAs($user)
            ->from(route('items.show', $item))
            ->post(route('items.comments.store', $item), [
                'body' => '',
            ]);

        $response->assertRedirect(route('items.show', $item));
        $response->assertSessionHasErrors('body');
        $this->assertEquals(0, $item->fresh()->comments_count);
    }

    /**
     * コメントが255字以上の場合、バリデーションメッセージが表示される
     */
    public function test_コメントが255字以上の場合_バリデーションメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem();

        $tooLong = str_repeat('あ', 256);

        $response = $this->actingAs($user)
            ->from(route('items.show', $item))
            ->post(route('items.comments.store', $item), [
                'body' => $tooLong,
            ]);

        $response->assertRedirect(route('items.show', $item));
        $response->assertSessionHasErrors('body');
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'body'    => $tooLong,
        ]);
        $this->assertEquals(0, $item->fresh()->comments_count);
    }
}