<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 小計画面で変更が反映される
     *
     * @return void
     */
    public function test_payment_method_selection_reflected_in_summary()
    {
        $user = User::factory()->create([
            'postal_code' => '123-4567',
            'address' => 'テスト住所',
        ]);
        $itemOwner = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'is_published' => true,
            'sold_at' => null,
        ]);
        $item->categories()->attach($category->id);

        $response = $this->actingAs($user)->get("/purchase/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('コンビニ払い', false);
        $response->assertSee('カード支払い', false);
    }
}

