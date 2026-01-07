<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemSellTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品出品画面にて必要な情報が保存できること
     *
     * @return void
     */
    public function test_can_register_item_with_all_required_fields()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category1 = Category::factory()->create(['name' => 'カテゴリー1']);
        $category2 = Category::factory()->create(['name' => 'カテゴリー2']);

        $image = UploadedFile::fake()->image('item.jpg');

        $response = $this->actingAs($user)->post('/sell', [
            'item_image' => [$image],
            'categories' => [$category1->id, $category2->id],
            'condition' => '新品',
            'name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'description' => '商品説明',
            'price' => 10000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'description' => '商品説明',
            'price' => 10000,
            'condition' => '新品',
            'is_published' => true,
        ]);

        $item = Item::where('name', 'テスト商品')->first();
        $this->assertTrue($item->categories->contains($category1));
        $this->assertTrue($item->categories->contains($category2));
    }
}

