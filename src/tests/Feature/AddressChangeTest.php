<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressChangeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     *
     * @return void
     */
    public function test_address_change_reflected_in_purchase_page()
    {
        $user = User::factory()->create([
            'postal_code' => '123-4567',
            'address' => '旧住所',
            'building_name' => '旧建物名',
        ]);
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
            'sold_at' => null,
        ]);
        $item->categories()->attach($category->id);

        // 住所を変更
        $response = $this->actingAs($user)->post("/purchase/address/{$item->id}", [
            'postal_code' => '987-6543',
            'address' => '新住所',
            'building_name' => '新建物名',
        ]);

        $response->assertRedirect(route('purchase.create', ['item' => $item->id]));
        $response->assertSessionHas('status', '住所を変更しました');

        // 商品購入画面で新しい住所が表示される
        $purchaseResponse = $this->actingAs($user)->get("/purchase/{$item->id}");
        $purchaseResponse->assertStatus(200);
        $purchaseResponse->assertSee('987-6543', false);
        $purchaseResponse->assertSee('新住所', false);
        $purchaseResponse->assertSee('新建物名', false);
    }

    /**
     * 購入した商品に送付先住所が紐づいて登録される
     *
     * @return void
     */
    public function test_address_linked_to_purchased_item()
    {
        $user = User::factory()->create([
            'postal_code' => '123-4567',
            'address' => 'テスト住所',
            'building_name' => 'テスト建物名',
        ]);
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
            'sold_at' => null,
        ]);
        $item->categories()->attach($category->id);

        // 住所を変更してから購入
        $this->actingAs($user)->post("/purchase/address/{$item->id}", [
            'postal_code' => '987-6543',
            'address' => '新住所',
            'building_name' => '新建物名',
        ]);

        $this->actingAs($user)->post("/purchase/{$item->id}", [
            'payment_method' => 'コンビニ払い',
        ]);

        // ユーザーの住所が更新されていることを確認
        $user->refresh();
        $this->assertEquals('987-6543', $user->postal_code);
        $this->assertEquals('新住所', $user->address);
        $this->assertEquals('新建物名', $user->building_name);
    }
}

