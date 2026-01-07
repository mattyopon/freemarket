<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ユーザーが存在しない場合は作成
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // 商品画像ディレクトリを作成
        $imageDir = 'items';
        if (!Storage::disk('public')->exists($imageDir)) {
            Storage::disk('public')->makeDirectory($imageDir);
        }

        // ダミー商品データ（10件）
        $items = [
            [
                'name' => '腕時計',
                'brand_name' => 'SEIKO',
                'description' => '美品の腕時計です。使用感はほとんどありません。',
                'price' => 15000,
                'condition' => '未使用に近い',
                'image' => $imageDir . '/watch.svg',
                'local_image' => 'Armani+Mens+Clock.jpg', // ローカル画像ファイル名
                'category_names' => ['アクセサリー', 'メンズ'],
            ],
            [
                'name' => 'HDD',
                'brand_name' => 'Western Digital',
                'description' => '1TBの外付けHDDです。動作確認済みです。',
                'price' => 5000,
                'condition' => '目立った傷や汚れなし',
                'image' => $imageDir . '/hdd.svg',
                'local_image' => 'HDD+Hard+Disk.jpg',
                'category_names' => ['家電'],
            ],
            [
                'name' => '玉ねぎ3束',
                'brand_name' => null,
                'description' => '新鮮な玉ねぎ3束です。家庭菜園で育てました。',
                'price' => 300,
                'condition' => '新品',
                'image' => $imageDir . '/onion.svg',
                'local_image' => 'iLoveIMG+d.jpg',
                'category_names' => ['キッチン'],
            ],
            [
                'name' => '革靴',
                'brand_name' => 'Clarks',
                'description' => 'レザーの革靴です。サイズは27cmです。',
                'price' => 8000,
                'condition' => 'やや傷や汚れあり',
                'image' => $imageDir . '/shoes.svg',
                'local_image' => 'Leather+Shoes+Product+Photo.jpg',
                'category_names' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'ノートPC',
                'brand_name' => 'Lenovo',
                'description' => 'Core i5搭載のノートPCです。メモリ8GB、SSD256GB。',
                'price' => 45000,
                'condition' => '目立った傷や汚れなし',
                'image' => $imageDir . '/laptop.svg',
                'local_image' => 'Living+Room+Laptop.jpg',
                'category_names' => ['家電'],
            ],
            [
                'name' => 'マイク',
                'brand_name' => 'Audio-Technica',
                'description' => 'コンデンサーマイクです。録音品質が良いです。',
                'price' => 12000,
                'condition' => '未使用に近い',
                'image' => $imageDir . '/microphone.svg',
                'local_image' => 'Music+Mic+4632231.jpg',
                'category_names' => ['家電'],
            ],
            [
                'name' => 'ショルダーバッグ',
                'brand_name' => '無印良品',
                'description' => 'シンプルなショルダーバッグです。軽量で使いやすいです。',
                'price' => 2500,
                'condition' => '目立った傷や汚れなし',
                'image' => $imageDir . '/bag.svg',
                'local_image' => 'Purse+fashion+pocket.jpg',
                'category_names' => ['ファッション', 'レディース'],
            ],
            [
                'name' => 'タンブラー',
                'brand_name' => 'スターバックス',
                'description' => '限定デザインのタンブラーです。保温性が良いです。',
                'price' => 1800,
                'condition' => '未使用に近い',
                'image' => $imageDir . '/tumbler.svg',
                'local_image' => 'Tumbler+souvenir.jpg',
                'category_names' => ['キッチン'],
            ],
            [
                'name' => 'コーヒーミル',
                'brand_name' => 'Hario',
                'description' => '手動式のコーヒーミルです。豆の挽き具合を調整できます。',
                'price' => 3500,
                'condition' => '目立った傷や汚れなし',
                'image' => $imageDir . '/coffee_mill.svg',
                'local_image' => 'Waitress+with+Coffee+Grinder.jpg',
                'category_names' => ['キッチン'],
            ],
            [
                'name' => 'メイクセット',
                'brand_name' => null,
                'description' => '便利なメイクアップセットです。',
                'price' => 2500,
                'condition' => '新品',
                'image' => $imageDir . '/makeup.svg',
                'local_image' => '外出メイクアップセット.jpg',
                'category_names' => ['コスメ', 'レディース'],
            ],
        ];

        foreach ($items as $index => $itemData) {
            // 画像パスをJPGに変更
            $originalImagePath = $itemData['image'];
            $imagePath = str_replace('.svg', '.jpg', $originalImagePath);
            $fullPath = Storage::disk('public')->path($imagePath);
            $dir = dirname($fullPath);
            
            // ディレクトリが存在しない場合は作成
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            
            // ローカルの画像ファイルをチェック（database/seeders/images/）
            $seederImageDir = database_path('seeders/images');
            // local_imageが指定されている場合はそれを使用、なければbasenameから推測
            $imageFileName = isset($itemData['local_image']) ? $itemData['local_image'] : basename($imagePath);
            $localImagePath = $seederImageDir . '/' . $imageFileName;
            
            $imageContent = false;
            
            // まずローカルの画像ファイルをチェック
            if (file_exists($localImagePath) && is_readable($localImagePath)) {
                $imageContent = @file_get_contents($localImagePath);
                if ($imageContent === false) {
                    // ファイルが読み取れない場合はログを出力（デバッグ用）
                    \Log::warning("Failed to read image file: {$localImagePath}");
                }
            } else {
                // ファイルが見つからない場合はログを出力（デバッグ用）
                \Log::warning("Image file not found: {$localImagePath}");
            }
            
            // ローカル画像が見つからない場合は、プレースホルダー画像サービスから画像を取得
            if ($imageContent === false) {
                $imageUrl = 'https://picsum.photos/400/400?random=' . ($index + 1);
                
                // 画像をダウンロード（cURLを使用）
                if (function_exists('curl_init')) {
                    $ch = curl_init($imageUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $imageContent = @curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode !== 200 || $imageContent === false) {
                        $imageContent = false;
                    }
                } else {
                    // cURLが使用できない場合はfile_get_contentsを試す
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 10,
                            'ignore_errors' => true,
                        ],
                    ]);
                    $imageContent = @file_get_contents($imageUrl, false, $context);
                }
            }
            
            if ($imageContent !== false && strlen($imageContent) > 0) {
                @file_put_contents($fullPath, $imageContent);
            } else {
                // ダウンロードに失敗した場合はSVGプレースホルダーを作成
                $imagePath = $originalImagePath; // SVGに戻す
                $fullPath = Storage::disk('public')->path($imagePath);
                $itemName = $itemData['name'];
                $svgContent = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
  <rect width="400" height="400" fill="#E0E0E0"/>
  <text x="200" y="200" font-family="Arial" font-size="24" fill="#999999" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars($itemName) . '</text>
</svg>';
                @file_put_contents($fullPath, $svgContent);
            }

            $item = Item::create([
                'user_id' => $user->id,
                'name' => $itemData['name'],
                'brand_name' => $itemData['brand_name'],
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                'condition' => $itemData['condition'],
                'image' => $imagePath,
                'is_published' => true,
            ]);

            // カテゴリーを関連付け（必ず設定）
            if (isset($itemData['category_names']) && is_array($itemData['category_names']) && count($itemData['category_names']) > 0) {
                $categoryIds = Category::whereIn('name', $itemData['category_names'])->pluck('id');
                if ($categoryIds->count() > 0) {
                    $item->categories()->attach($categoryIds);
                }
            } else {
                // カテゴリーが指定されていない場合はランダムに設定
                $allCategories = Category::all();
                if ($allCategories->count() > 0) {
                    $randomCategories = $allCategories->random(min(2, $allCategories->count()));
                    $item->categories()->attach($randomCategories->pluck('id'));
                }
            }
        }
    }
}
