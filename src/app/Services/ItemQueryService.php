<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ItemQueryService
{
    /**
     * いいねした商品を取得
     *
     * @param  \App\Models\User|null  $user
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLikedItems($user, $search)
    {
        if (!$user) {
            // 未ログインの場合は何も表示されない
            return collect();
        }

        $itemIds = $user->likedItems()->where('is_published', true)->pluck('items.id')->toArray();

        if (empty($itemIds)) {
            return collect();
        }

        $query = Item::with('purchase')->whereIn('id', $itemIds)
            ->where('is_published', true);
        
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->get();
    }

    /**
     * 出品した商品を取得
     *
     * @param  \App\Models\User|null  $user
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getListedItems($user, $search)
    {
        if (!$user) {
            return collect();
        }

        $query = Item::with('purchase')->where('user_id', $user->id);
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        return $query->get();
    }

    /**
     * 購入した商品を取得
     *
     * @param  \App\Models\User|null  $user
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPurchasedItems($user, $search)
    {
        if (!$user) {
            return collect();
        }

        $purchaseItemIds = Purchase::where('user_id', $user->id)->pluck('item_id');
        $query = Item::with('purchase')->whereIn('id', $purchaseItemIds);
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        return $query->get();
    }

    /**
     * おすすめ商品を取得
     *
     * @param  \App\Models\User|null  $user
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecommendedItems($user, $search)
    {
        $query = Item::with('purchase')->where('is_published', true);
        if ($user) {
            $query->where('user_id', '!=', $user->id);
        }
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        return $query->get();
    }
}

