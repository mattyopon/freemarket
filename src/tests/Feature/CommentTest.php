<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログイン済みのユーザーはコメントを送信できる
     *
     * @return void
     */
    public function test_authenticated_user_can_post_comment()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        $initialCommentCount = $item->comments()->count();

        $response = $this->actingAs($user)->post("/items/{$item->id}/comments", [
            'content' => 'テストコメント',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'テストコメント',
        ]);
        $this->assertEquals($initialCommentCount + 1, $item->fresh()->comments()->count());
    }

    /**
     * ログイン前のユーザーはコメントを送信できない
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_post_comment()
    {
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        $response = $this->post("/items/{$item->id}/comments", [
            'content' => 'テストコメント',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'content' => 'テストコメント',
        ]);
    }

    /**
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     *
     * @return void
     */
    public function test_comment_validation_required()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        $response = $this->actingAs($user)->post("/items/{$item->id}/comments", [
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    /**
     * コメントが255字以上の場合、バリデーションメッセージが表示される
     *
     * @return void
     */
    public function test_comment_validation_max_length()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        $longComment = str_repeat('a', 256);

        $response = $this->actingAs($user)->post("/items/{$item->id}/comments", [
            'content' => $longComment,
        ]);

        $response->assertSessionHasErrors(['content']);
    }
}

