<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Item;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class ItemCommentController extends Controller
{
    /**
     * コメントを保存
     *
     * @param  \App\Http\Requests\CommentRequest  $request
     * @param  int  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CommentRequest $request, $item)
    {
        $item = Item::findOrFail($item);

        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
            'content' => $request->content,
        ]);

        return redirect()->route('items.show', ['item' => $item->id])->with('status', 'コメントを送信しました');
    }
}
