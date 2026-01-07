@extends('layouts.app')

@section('title', '商品一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/items/index.css') }}">
@endsection

@section('content')
<div class="items-page">
    <div class="items-page__header">
        <div class="items-page__logo">
            <a href="{{ route('items.index') }}" class="items-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="items-page__logo-img">
            </a>
        </div>
        <div class="items-page__search">
            <form method="GET" action="{{ Auth::check() ? route('mypage') : route('items.index') }}" class="items-page__search-form">
                <div class="items-page__search-wrapper">
                    <input type="text" name="search" class="items-page__search-input" placeholder="なにをお探しですか?" value="{{ $search ?? '' }}">
                    <button type="submit" class="items-page__search-button">検索</button>
                </div>
                @auth
                    @if($tab !== 'recommended')
                        @php
                            $pageMap = [
                                'purchased' => 'buy',
                                'listed' => 'sell',
                                'mylist' => 'mylist',
                            ];
                            $page = $pageMap[$tab] ?? null;
                        @endphp
                        @if($page)
                            <input type="hidden" name="page" value="{{ $page }}">
                        @endif
                    @endif
                @else
                    @if($tab !== 'recommended')
                        <input type="hidden" name="tab" value="{{ $tab }}">
                    @endif
                @endauth
            </form>
        </div>
        <div class="items-page__nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="items-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="items-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="items-page__nav-button">出品</a>
            @else
            <a href="{{ route('login') }}" class="items-page__nav-link">ログイン</a>
            @endauth
        </div>
    </div>

    @auth
    <div class="items-page__profile">
        <div class="items-page__profile-image">
            @if($user->profile_image)
                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像" class="items-page__profile-img">
            @else
                <div class="items-page__profile-placeholder"></div>
            @endif
        </div>
        <div class="items-page__profile-name">{{ $user->name }}</div>
        <a href="{{ route('profile.edit') }}" class="items-page__profile-edit-button">プロフィールを編集</a>
    </div>
    @endauth

    <div class="items-page__tabs">
        @auth
        <a href="{{ route('mypage', $search ? ['search' => $search] : []) }}" class="items-page__tab {{ $tab === 'recommended' ? 'items-page__tab--active' : '' }}">おすすめ</a>
        <a href="{{ route('mypage', array_merge(['tab' => 'mylist'], $search ? ['search' => $search] : [])) }}" class="items-page__tab {{ $tab === 'mylist' ? 'items-page__tab--active' : '' }}">マイリスト</a>
        <a href="{{ route('mypage', array_merge(['tab' => 'sell'], $search ? ['search' => $search] : [])) }}" class="items-page__tab {{ $tab === 'listed' ? 'items-page__tab--active' : '' }}">出品した商品</a>
        <a href="{{ route('mypage', array_merge(['tab' => 'buy'], $search ? ['search' => $search] : [])) }}" class="items-page__tab {{ $tab === 'purchased' ? 'items-page__tab--active' : '' }}">購入した商品</a>
        @else
        <a href="{{ route('items.index', $search ? ['search' => $search] : []) }}" class="items-page__tab {{ $tab === 'recommended' ? 'items-page__tab--active' : '' }}">おすすめ</a>
        <a href="{{ route('items.index', array_merge(['tab' => 'mylist'], $search ? ['search' => $search] : [])) }}" class="items-page__tab {{ $tab === 'mylist' ? 'items-page__tab--active' : '' }}">マイリスト</a>
        @endauth
    </div>

    <div class="items-page__content">
        @if($items->count() > 0)
        <div class="items-grid">
            @foreach($items as $item)
            <div class="items-grid__item">
                <a href="{{ route('items.show', ['item' => $item->id]) }}" class="items-grid__item-link">
                    <div class="items-grid__item-image">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="items-grid__item-img">
                        @else
                            <div class="items-grid__item-placeholder">商品画像</div>
                        @endif
                        @if($item->isSold())
                            <div class="items-grid__item-sold">Sold</div>
                        @endif
                        @if($tab === 'listed')
                            @if($item->is_published)
                                <div class="items-grid__item-status items-grid__item-status--published">公開中</div>
                            @else
                                <div class="items-grid__item-status items-grid__item-status--unpublished">公開停止</div>
                            @endif
                        @endif
                    </div>
                    <div class="items-grid__item-name">{{ $item->name }}</div>
                </a>
                @php
                    $isLiked = $item->is_liked ?? false;
                @endphp
                <button type="button" class="items-grid__item-like-button {{ $isLiked ? 'items-grid__item-like-button--liked' : '' }}" data-item-id="{{ $item->id }}" data-liked="{{ $isLiked ? 'true' : 'false' }}" onclick="event.preventDefault(); toggleLike({{ $item->id }}, this);">
                    <img src="{{ asset('images/' . ($isLiked ? 'heart-red.png' : 'heart-gray.png')) }}" alt="いいね" class="items-grid__item-like-icon">
                    <span class="items-grid__item-like-count">{{ $item->likes->count() }}</span>
                </button>
            </div>
            @endforeach
        </div>
        @else
        <div class="items-page__empty">
            <p class="items-page__empty-text">商品がありません</p>
        </div>
        @endif
    </div>
</div>

<script>
function toggleLike(itemId, button) {
    const isLiked = button.getAttribute('data-liked') === 'true';
    const url = isLiked 
        ? `/items/${itemId}/like` 
        : `/items/${itemId}/like`;
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
            const icon = button.querySelector('.items-grid__item-like-icon');
            const count = button.querySelector('.items-grid__item-like-count');
            
            if (isLiked) {
                icon.src = '{{ asset("images/heart-gray.png") }}';
                button.classList.remove('items-grid__item-like-button--liked');
                button.setAttribute('data-liked', 'false');
            } else {
                icon.src = '{{ asset("images/heart-red.png") }}';
                button.classList.add('items-grid__item-like-button--liked');
                button.setAttribute('data-liked', 'true');
            }
            
            count.textContent = data.likes_count;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endsection

