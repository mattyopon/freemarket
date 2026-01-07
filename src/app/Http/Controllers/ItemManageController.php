<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemManageController extends Controller
{
    /**
     * 商品管理画面を表示
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $item = Item::with('categories')->findOrFail($id);
        
        // 出品者のみアクセス可能
        if ($item->user_id !== Auth::id()) {
            abort(403);
        }

        $categories = Category::all();
        $selectedCategoryIds = $item->categories->pluck('id')->toArray();
        
        return view('items.manage', compact('item', 'categories', 'selectedCategoryIds'));
    }

    /**
     * 商品情報を更新
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        
        // 出品者のみ更新可能
        if ($item->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->only(['name', 'brand_name', 'description', 'price', 'condition']);
        $data['is_published'] = $request->has('is_published') ? true : false;
        
        // 商品画像の更新
        if ($request->hasFile('item_image') && $request->file('item_image')[0]) {
            // 既存の画像を削除
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                @Storage::disk('public')->delete($item->image);
            }
            
            $file = $request->file('item_image')[0];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $directory = 'items';
            $publicPath = Storage::disk('public')->path($directory);
            
            if (!is_dir($publicPath)) {
                @mkdir($publicPath, 0775, true);
            }
            
            if (@move_uploaded_file($file->getRealPath(), $publicPath . '/' . $filename)) {
                $data['image'] = $directory . '/' . $filename;
            }
        }

        $item->update($data);

        // カテゴリーを更新
        if ($request->has('categories')) {
            $item->categories()->sync($request->categories);
        } else {
            $item->categories()->detach();
        }

        return redirect()->route('items.show', ['item' => $item->id])->with('status', '商品情報を更新しました');
    }
}
