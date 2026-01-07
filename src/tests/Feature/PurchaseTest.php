<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 「購入する」ボタンを押下すると購入が完了する
     *
     * @return void
     */
    public function test_can_purchase_item()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
            'sold_at' => null,
        ]);
        $item->categories()->attach($category->id);

        $response = $this->actingAs($user)->post("/purchase/{$item->id}", [
            'payment_method' => 'コンビニ払い',
        ]);

        $response->assertRedirect(route('purchase.complete', ['item' => $item->id]));
        $this->assertNotNull($item->fresh()->sold_at);
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
        ]);
    }

    /**
     * 購入した商品は商品一覧画面にて「sold」と表示される
     *
     * @return void
     */
    public function test_purchased_item_shows_sold_label()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
            'sold_at' => now(),
        ]);
        $item->categories()->attach($category->id);

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
            'purchased_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sold', false);
    }

    /**
     * 「プロフィール/購入した商品一覧」に追加されている
     *
     * @return void
     */
    public function test_purchased_item_appears_in_purchased_list()
    {
        $user = User::factory()->create();
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'name' => '購入した商品',
            'is_published' => true,
            'sold_at' => now(),
        ]);
        $item->categories()->attach($category->id);

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/?tab=purchased');

        $response->assertStatus(200);
        $response->assertSee('購入した商品', false);
    }
}

