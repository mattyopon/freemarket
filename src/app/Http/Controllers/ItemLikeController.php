<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemLikeController extends Controller
{
    /**
     * いいねを追加
     *
     * @param  int  $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($item)
    {
        $item = Item::findOrFail($item);
        $user = Auth::user();

        if ($user) {
            // ログイン済みの場合、データベースに保存
            if (!$user->likedItems()->where('items.id', $item->id)->exists()) {
                $user->likedItems()->attach($item->id);
            }
        } else {
            // 未ログインの場合、セッションに保存
            $likedItems = session('liked_items', []);
            if (!in_array($item->id, $likedItems)) {
                $likedItems[] = $item->id;
                session(['liked_items' => $likedItems]);
            }
        }

        $likesCount = $item->likes()->count();

        return response()->json([
            'success' => true,
            'likes_count' => $likesCount,
        ]);
    }

    /**
     * いいねを解除
     *
     * @param  int  $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($item)
    {
        $item = Item::findOrFail($item);
        $user = Auth::user();

        if ($user) {
            // ログイン済みの場合、データベースから削除
            $user->likedItems()->detach($item->id);
        } else {
            // 未ログインの場合、セッションから削除
            $likedItems = session('liked_items', []);
            $likedItems = array_values(array_filter($likedItems, function($id) use ($item) {
                return $id != $item->id;
            }));
            session(['liked_items' => $likedItems]);
        }

        $likesCount = $item->likes()->count();

        return response()->json([
            'success' => true,
            'likes_count' => $likesCount,
        ]);
    }
}

