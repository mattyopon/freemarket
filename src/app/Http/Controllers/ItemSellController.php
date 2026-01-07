<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemSellController extends Controller
{
    /**
     * 商品出品画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();
        return view('items.sell', compact('categories'));
    }

    /**
     * 商品を登録
     *
     * @param  \App\Http\Requests\ExhibitionRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExhibitionRequest $request)
    {
        $user = Auth::user();
        
        // 商品画像を保存
        $imagePath = null;
        if ($request->hasFile('item_image') && $request->file('item_image')[0]) {
            $file = $request->file('item_image')[0];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $directory = 'items';
            $publicPath = Storage::disk('public')->path($directory);
            
            if (!is_dir($publicPath)) {
                @mkdir($publicPath, 0775, true);
            }
            
            if (@move_uploaded_file($file->getRealPath(), $publicPath . '/' . $filename)) {
                $imagePath = $directory . '/' . $filename;
            }
        }

        // 商品を作成
        $item = Item::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'brand_name' => $request->brand_name,
            'description' => $request->description,
            'price' => $request->price,
            'condition' => $request->condition,
            'image' => $imagePath ?? 'items/placeholder.svg',
            'is_published' => true,
        ]);

        // カテゴリーを関連付け
        if ($request->has('categories')) {
            $item->categories()->attach($request->categories);
        }

        return redirect()->route('items.sell.complete', ['item' => $item->id]);
    }

    /**
     * 商品登録完了画面を表示
     *
     * @param  int  $item
     * @return \Illuminate\View\View
     */
    public function complete($item)
    {
        $item = Item::findOrFail($item);
        return view('items.sell-complete', compact('item'));
    }
}
