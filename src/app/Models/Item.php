<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'brand_name',
        'description',
        'price',
        'condition',
        'image',
        'sold_at',
        'is_published',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    /**
     * 出品者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * いいね
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * 購入情報
     */
    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    /**
     * コメント
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * カテゴリー
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_category');
    }

    /**
     * 購入済みかどうか
     */
    public function isSold()
    {
        // purchaseリレーションがロードされている場合はそれを使用、そうでない場合はクエリを実行
        if ($this->relationLoaded('purchase')) {
            return $this->purchase !== null || $this->sold_at !== null;
        }
        return $this->purchase()->exists() || $this->sold_at !== null;
    }
}
