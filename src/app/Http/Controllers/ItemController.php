<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ItemQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * 商品クエリサービス
     *
     * @var \App\Services\ItemQueryService
     */
    private $itemQueryService;

    /**
     * コンストラクタ
     *
     * @param  \App\Services\ItemQueryService  $itemQueryService
     * @return void
     */
    public function __construct(ItemQueryService $itemQueryService)
    {
        $this->itemQueryService = $itemQueryService;
    }

    /**
     * 商品一覧画面を表示
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tab = $this->normalizeTabParameter($request);
        $user = Auth::user();
        $search = $request->get('search');

        $items = $this->getItemsByTab($tab, $user, $search);
        $items = $this->attachLikeStatus($items, $user);

        return view('items.index', compact('items', 'tab', 'search', 'user'));
    }

    /**
     * タブパラメータを正規化
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function normalizeTabParameter(Request $request)
    {
        $tab = $request->get('tab');
        if (!$tab) {
            $page = $request->get('page');
            $tab = $page ? $this->convertPageToTab($page) : 'recommended';
        }
        return $this->convertTabToInternal($tab);
    }

    /**
     * pageパラメータをtabパラメータに変換
     *
     * @param  string  $page
     * @return string
     */
    private function convertPageToTab($page)
    {
        $tabMap = ['buy' => 'purchased', 'sell' => 'listed', 'mylist' => 'mylist'];
        return $tabMap[$page] ?? 'recommended';
    }

    /**
     * tabパラメータを内部的な値に変換
     *
     * @param  string  $tab
     * @return string
     */
    private function convertTabToInternal($tab)
    {
        $tabMap = [
            'buy' => 'purchased',
            'sell' => 'listed',
            'mylist' => 'mylist',
            'recommended' => 'recommended',
        ];
        return $tabMap[$tab] ?? $tab;
    }

    /**
     * タブに応じた商品を取得
     *
     * @param  string  $tab
     * @param  \App\Models\User|null  $user
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getItemsByTab($tab, $user, $search)
    {
        switch ($tab) {
            case 'mylist':
                return $this->itemQueryService->getLikedItems($user, $search);
            case 'listed':
                return $this->itemQueryService->getListedItems($user, $search);
            case 'purchased':
                return $this->itemQueryService->getPurchasedItems($user, $search);
            case 'recommended':
            default:
                return $this->itemQueryService->getRecommendedItems($user, $search);
        }
    }

    /**
     * 商品にいいね状態を付与
     *
     * @param  \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection  $items
     * @param  \App\Models\User|null  $user
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function attachLikeStatus($items, $user)
    {
        // Eloquent\Collectionの場合のみload()を呼び出す
        if ($items instanceof \Illuminate\Database\Eloquent\Collection && $items->isNotEmpty()) {
            $items = $items->load('likes');
        }

        $likedItemIds = [];

        if ($user) {
            // ログイン済みの場合、データベースから取得
            $likedItemIds = $user->likedItems()->pluck('items.id')->toArray();
        } else {
            // 未ログインの場合、セッションから取得
            $likedItemIds = session('liked_items', []);
        }

        $items = $items->map(function($item) use ($likedItemIds) {
            $item->is_liked = in_array($item->id, $likedItemIds);
            return $item;
        });

        return $items;
    }

    /**
     * 商品詳細画面を表示
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $item = Item::with(['user', 'categories', 'comments.user', 'likes', 'purchase'])->findOrFail($id);
        $user = Auth::user();
        $isOwner = $user && $item->user_id === $user->id;
        
        // 出品者以外は公開されている商品のみ閲覧可能
        if (!$isOwner && !$item->is_published) {
            abort(404);
        }
        
        // いいね状態を取得（ログイン時はDB、未ログイン時はセッション）
        if ($user) {
            $isLiked = $user->likedItems()->where('items.id', $item->id)->exists();
        } else {
            $likedItems = session('liked_items', []);
            $isLiked = in_array($item->id, $likedItems);
        }
        
        return view('items.show', compact('item', 'user', 'isOwner', 'isLiked'));
    }
}
