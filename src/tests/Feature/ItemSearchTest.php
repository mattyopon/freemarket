<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 「商品名」で部分一致検索ができる
     *
     * @return void
     */
    public function test_can_search_items_by_name()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item1 = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '腕時計',
            'is_published' => true,
        ]);
        $item1->categories()->attach($category->id);

        $item2 = Item::factory()->create([
            'user_id' => $user->id,
            'name' => 'HDD',
            'is_published' => true,
        ]);
        $item2->categories()->attach($category->id);

        $response = $this->get('/?search=腕時計');

        $response->assertStatus(200);
        $response->assertSee('腕時計', false);
        $response->assertDontSee('HDD', false);
    }

    /**
     * 検索状態がマイリストでも保持されている
     *
     * @return void
     */
    public function test_search_state_preserved_in_mylist()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $otherUser->id,
            'name' => '検索商品',
            'is_published' => true,
        ]);
        $item->categories()->attach($category->id);

        \App\Models\Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist&search=検索商品');

        $response->assertStatus(200);
        $response->assertSee('検索商品', false);
        $response->assertSee('value="検索商品"', false); // 検索キーワードが保持されている
    }
}

