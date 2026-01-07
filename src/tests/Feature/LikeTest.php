<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねアイコンを押下することによって、いいねした商品として登録することができる
     *
     * @return void
     */
    public function test_can_like_item()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        $initialLikeCount = $item->likes()->count();

        $response = $this->actingAs($user)->post("/items/{$item->id}/like");

        $response->assertRedirect();
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
        $this->assertEquals($initialLikeCount + 1, $item->fresh()->likes()->count());
    }

    /**
     * 追加済みのアイコンは色が変化する
     *
     * @return void
     */
    public function test_liked_icon_changes_color()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get("/items/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('item-show-info__like-icon--liked', false);
    }

    /**
     * 再度いいねアイコンを押下することによって、いいねを解除することができる
     *
     * @return void
     */
    public function test_can_unlike_item()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $initialLikeCount = $item->likes()->count();

        $response = $this->actingAs($user)->delete("/items/{$item->id}/like");

        $response->assertRedirect();
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
        $this->assertEquals($initialLikeCount - 1, $item->fresh()->likes()->count());
    }
}

