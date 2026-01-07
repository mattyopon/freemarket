<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 必要な情報が取得できる
     *
     * @return void
     */
    public function test_can_get_user_profile_information()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'profile_image' => 'profiles/test.jpg',
        ]);

        $category = Category::factory()->create(['name' => 'テストカテゴリー']);

        $listedItem = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '出品した商品',
            'is_published' => true,
        ]);
        $listedItem->categories()->attach($category->id);

        $purchasedItem = Item::factory()->create([
            'user_id' => User::factory()->create()->id,
            'name' => '購入した商品',
            'is_published' => true,
            'sold_at' => now(),
        ]);
        $purchasedItem->categories()->attach($category->id);

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $purchasedItem->id,
            'payment_method' => 'コンビニ払い',
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/?tab=listed');

        $response->assertStatus(200);
        $response->assertSee('テストユーザー', false);
        $response->assertSee('出品した商品', false);
    }

    /**
     * 変更項目が初期値として過去設定されていること
     *
     * @return void
     */
    public function test_profile_edit_shows_initial_values()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'postal_code' => '123-4567',
            'address' => 'テスト住所',
            'building_name' => 'テスト建物名',
            'profile_image' => 'profiles/test.jpg',
        ]);

        $response = $this->actingAs($user)->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee('value="テストユーザー"', false);
        $response->assertSee('value="123-4567"', false);
        $response->assertSee('value="テスト住所"', false);
        $response->assertSee('value="テスト建物名"', false);
    }
}

