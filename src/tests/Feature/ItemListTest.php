<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 全商品を取得できる
     *
     * @return void
     */
    public function test_can_get_all_items()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item1 = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '商品1',
            'is_published' => true,
        ]);
        $item1->categories()->attach($category->id);

        $item2 = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '商品2',
            'is_published' => true,
        ]);
        $item2->categories()->attach($category->id);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('items.index');
        $response->assertViewHas('items');
    }

    /**
     * 購入済み商品は「Sold」と表示される
     *
     * @return void
     */
    public function test_sold_items_display_sold_label()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '購入済み商品',
            'is_published' => true,
            'sold_at' => now(),
        ]);
        $item->categories()->attach($category->id);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sold', false);
    }

    /**
     * 自分が出品した商品は表示されない
     *
     * @return void
     */
    public function test_own_items_not_displayed_in_recommended()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $ownItem = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '自分の商品',
            'is_published' => true,
        ]);
        $ownItem->categories()->attach($category->id);

        $otherItem = Item::factory()->create([
            'user_id' => $otherUser->id,
            'name' => '他人の商品',
            'is_published' => true,
        ]);
        $otherItem->categories()->attach($category->id);

        $response = $this->actingAs($user)->get('/?tab=recommended');

        $response->assertStatus(200);
        $response->assertDontSee('自分の商品', false);
        $response->assertSee('他人の商品', false);
    }

    /**
     * 未認証ユーザーにも商品一覧が表示される
     *
     * @return void
     */
    public function test_items_displayed_for_unauthenticated_users()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '公開商品',
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('公開商品', false);
    }
}

