<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねした商品だけが表示される
     *
     * @return void
     */
    public function test_only_liked_items_displayed()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $likedItem = Item::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'いいねした商品',
            'is_published' => true,
        ]);
        $likedItem->categories()->attach($category->id);

        $notLikedItem = Item::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'いいねしていない商品',
            'is_published' => true,
        ]);
        $notLikedItem->categories()->attach($category->id);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('いいねした商品', false);
        $response->assertDontSee('いいねしていない商品', false);
    }

    /**
     * 購入済み商品は「Sold」と表示される
     *
     * @return void
     */
    public function test_sold_items_display_sold_label_in_mylist()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $soldItem = Item::factory()->create([
            'user_id' => $otherUser->id,
            'name' => '購入済み商品',
            'is_published' => true,
            'sold_at' => now(),
        ]);
        $soldItem->categories()->attach($category->id);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $soldItem->id,
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('Sold', false);
    }

    /**
     * 未認証の場合は何も表示されない
     *
     * @return void
     */
    public function test_mylist_empty_for_unauthenticated_users()
    {
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertViewHas('items');
        $items = $response->viewData('items');
        $this->assertTrue($items->isEmpty());
    }
}

