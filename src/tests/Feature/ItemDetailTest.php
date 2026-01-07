<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 必要な情報が表示される
     *
     * @return void
     */
    public function test_item_detail_displays_all_required_information()
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->create(['name' => 'カテゴリー1']);
        $category2 = Category::factory()->create(['name' => 'カテゴリー2']);

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'description' => '商品説明',
            'price' => 10000,
            'condition' => '新品',
            'is_published' => true,
        ]);
        $item->categories()->attach([$category1->id, $category2->id]);

        $commentUser = User::factory()->create();
        Comment::create([
            'user_id' => $commentUser->id,
            'item_id' => $item->id,
            'content' => 'テストコメント',
        ]);

        Like::create([
            'user_id' => $commentUser->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get("/items/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト商品', false);
        $response->assertSee('テストブランド', false);
        $response->assertSee('10,000', false);
        $response->assertSee('商品説明', false);
        $response->assertSee('カテゴリー1', false);
        $response->assertSee('カテゴリー2', false);
        $response->assertSee('新品', false);
        $response->assertSee('テストコメント', false);
    }

    /**
     * 複数選択されたカテゴリが表示されているか
     *
     * @return void
     */
    public function test_multiple_categories_displayed()
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->create(['name' => 'カテゴリー1']);
        $category2 = Category::factory()->create(['name' => 'カテゴリー2']);
        $category3 = Category::factory()->create(['name' => 'カテゴリー3']);

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'is_published' => true,
        ]);
        $item->categories()->attach([$category1->id, $category2->id, $category3->id]);

        $response = $this->get("/items/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('カテゴリー1', false);
        $response->assertSee('カテゴリー2', false);
        $response->assertSee('カテゴリー3', false);
    }
}

