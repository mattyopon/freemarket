@extends('layouts.app')

@section('title', $item->name)

@section('css')
<link rel="stylesheet" href="{{ asset('css/items/show.css') }}">
@endsection

@section('content')
<div class="item-show-page">
    <div class="item-show-page__header">
        <div class="item-show-page__logo">
            <a href="{{ route('items.index') }}" class="item-show-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="item-show-page__logo-img">
            </a>
        </div>
        <div class="item-show-page__search">
            <form method="GET" action="{{ route('items.index') }}" class="item-show-page__search-form">
                <div class="item-show-page__search-wrapper">
                    <input type="text" name="search" class="item-show-page__search-input" placeholder="なにをお探しですか?" value="{{ request('search') }}">
                    <button type="submit" class="item-show-page__search-button">検索</button>
                </div>
            </form>
        </div>
        <div class="item-show-page__nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="item-show-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="item-show-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="item-show-page__nav-button">出品</a>
            @else
            <a href="{{ route('login') }}" class="item-show-page__nav-link">ログイン</a>
            @endauth
        </div>
    </div>

    <div class="item-show-container">
        @if (session('status'))
        <div class="item-show-message">
            <p class="item-show-message__text">{{ session('status') }}</p>
        </div>
        @endif

        <div class="item-show-main">
            <!-- 左側：商品画像 -->
            <div class="item-show-image">
                @if($item->image)
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-show-image__img">
                @else
                    <div class="item-show-image__placeholder">商品画像</div>
                @endif
            </div>

            <!-- 右側：商品情報と詳細 -->
            <div class="item-show-right">
                <!-- 商品基本情報 -->
                <div class="item-show-info">
                    <h1 class="item-show-info__name">{{ $item->name }}</h1>
                    @if($item->brand_name)
                    <p class="item-show-info__brand">{{ $item->brand_name }}</p>
                    @endif
                    <p class="item-show-info__price">¥{{ number_format($item->price) }}<span class="item-show-info__price-tax">(税込)</span></p>
                    
                <div class="item-show-info__actions">
                    <button type="button" class="item-show-info__like-button {{ $isLiked ? 'item-show-info__like-button--liked' : '' }}" data-item-id="{{ $item->id }}" data-liked="{{ $isLiked ? 'true' : 'false' }}">
                        <img src="{{ asset('images/' . ($isLiked ? 'heart-red.png' : 'heart-gray.png')) }}" alt="いいね" class="item-show-info__like-icon">
                        <span class="item-show-info__like-count">{{ $item->likes->count() }}</span>
                    </button>
                    <div class="item-show-info__comment">
                        <img src="{{ asset('images/comment.png') }}" alt="コメント" class="item-show-info__comment-icon">
                        <span class="item-show-info__comment-count">{{ $item->comments->count() }}</span>
                    </div>
                </div>

                    @if($isOwner)
                    <a href="{{ route('items.manage', ['item' => $item->id]) }}" class="item-show-info__manage-button">商品を管理する</a>
                    @else
                    <button type="button" class="item-show-info__purchase-button" onclick="handlePurchaseClick({{ Auth::check() ? 'true' : 'false' }}, '{{ route('purchase.create', ['item' => $item->id]) }}', '{{ route('login') }}')">購入手続きへ</button>
                    @endif
                </div>

                <!-- 商品説明 -->
                <div class="item-show-section">
                    <h2 class="item-show-section__title">商品説明</h2>
                    <div class="item-show-section__content">
                        <p class="item-show-section__text">{{ $item->description }}</p>
                    </div>
                </div>

                <!-- 商品の情報 -->
                <div class="item-show-section">
                    <h2 class="item-show-section__title">商品の情報</h2>
                    <div class="item-show-section__content">
                        <div class="item-show-info-row">
                            <span class="item-show-info-row__label">カテゴリー</span>
                            <div class="item-show-info-row__value">
                                @forelse($item->categories as $category)
                                <span class="item-show-info-row__category-tag">{{ $category->name }}</span>
                                @empty
                                <span class="item-show-info-row__empty">カテゴリーが設定されていません</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="item-show-info-row">
                            <span class="item-show-info-row__label">商品の状態</span>
                            <span class="item-show-info-row__value">{{ $item->condition }}</span>
                        </div>
                    </div>
                </div>

                <!-- コメント -->
                <div class="item-show-section">
                    <h2 class="item-show-section__title">コメント({{ $item->comments->count() }})</h2>
                    <div class="item-show-comments">
                        @foreach($item->comments as $comment)
                        <div class="item-show-comment">
                            <div class="item-show-comment__user">
                                <div class="item-show-comment__avatar">
                                    @if($comment->user->profile_image)
                                        <img src="{{ asset('storage/' . $comment->user->profile_image) }}" alt="{{ $comment->user->name }}" class="item-show-comment__avatar-img">
                                    @else
                                        <div class="item-show-comment__avatar-placeholder"></div>
                                    @endif
                                </div>
                                <span class="item-show-comment__username">{{ $comment->user->name }}</span>
                            </div>
                            <div class="item-show-comment__content">
                                <p class="item-show-comment__text">{{ $comment->content }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="item-show-comment-form">
                        <h3 class="item-show-comment-form__title">商品へのコメント</h3>
                        @auth
                        <form method="POST" action="{{ route('items.comments.store', ['item' => $item->id]) }}" class="item-show-comment-form__form">
                            @csrf
                            <textarea name="content" class="item-show-comment-form__textarea @error('content') item-show-comment-form__textarea--error @enderror" rows="5" placeholder="コメントを入力してください" required>{{ old('content') }}</textarea>
                            @error('content')
                            <p class="item-show-comment-form__error">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="item-show-comment-form__submit">コメントを送信する</button>
                        </form>
                        @else
                        <div class="item-show-comment-form__login-prompt">
                            <p class="item-show-comment-form__login-message">コメントするにはログインが必要です</p>
                            <a href="{{ route('login') }}" class="item-show-comment-form__login-link">ログインページへ</a>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButton = document.querySelector('.item-show-info__like-button');
    if (likeButton) {
        likeButton.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const isLiked = this.getAttribute('data-liked') === 'true';
            const url = `/items/${itemId}/like`;
            const method = isLiked ? 'DELETE' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = this.querySelector('.item-show-info__like-icon');
                    const count = this.querySelector('.item-show-info__like-count');
                    
                    if (isLiked) {
                        icon.src = '{{ asset("images/heart-gray.png") }}';
                        this.classList.remove('item-show-info__like-button--liked');
                        this.setAttribute('data-liked', 'false');
                    } else {
                        icon.src = '{{ asset("images/heart-red.png") }}';
                        this.classList.add('item-show-info__like-button--liked');
                        this.setAttribute('data-liked', 'true');
                    }
                    
                    count.textContent = data.likes_count;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});

function handlePurchaseClick(isAuthenticated, purchaseUrl, loginUrl) {
    if (isAuthenticated) {
        window.location.href = purchaseUrl;
    } else {
        if (confirm('購入手続きにはログインが必要です。ログインページに移動しますか？')) {
            window.location.href = loginUrl;
        }
    }
}
</script>
@endsection

