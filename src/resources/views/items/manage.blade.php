@extends('layouts.app')

@section('title', '商品を管理する')

@section('css')
<link rel="stylesheet" href="{{ asset('css/items/manage.css') }}">
@endsection

@section('content')
<div class="item-manage-page">
    <div class="item-manage-page__header">
        <div class="item-manage-page__logo">
            <a href="{{ route('items.index') }}" class="item-manage-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="item-manage-page__logo-img">
            </a>
        </div>
        <div class="item-manage-page__search">
            <form method="GET" action="{{ route('mypage') }}" class="item-manage-page__search-form">
                <div class="item-manage-page__search-wrapper">
                    <input type="text" name="search" class="item-manage-page__search-input" placeholder="なにをお探しですか?" value="{{ request('search') }}">
                    <button type="submit" class="item-manage-page__search-button">検索</button>
                </div>
            </form>
        </div>
        <div class="item-manage-page__nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="item-manage-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="item-manage-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="item-manage-page__nav-button">出品</a>
            @else
            <a href="{{ route('login') }}" class="item-manage-page__nav-link">ログイン</a>
            @endauth
        </div>
    </div>

    <div class="item-manage-container">
        <h1 class="item-manage-container__title">商品を管理する</h1>
        
        <form class="item-manage-form" method="POST" action="{{ route('items.manage.update', ['item' => $item->id]) }}" enctype="multipart/form-data">
            @csrf
            
            <div class="item-manage-main">
                <!-- 左側：商品画像 -->
                <div class="item-manage-image">
                    <div class="item-manage-image__preview">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像" class="item-manage-image__preview-img" id="imagePreview">
                        @else
                            <div class="item-manage-image__placeholder" id="imagePreview">商品画像</div>
                        @endif
                    </div>
                    <label for="item_image" class="item-manage-image__button">
                        画像を選択する
                        <input type="file" id="item_image" name="item_image[]" accept="image/*" style="display: none;" onchange="previewImages(this)">
                    </label>
                </div>

                <!-- 右側：商品情報 -->
                <div class="item-manage-info">
                    <div class="item-manage-info__field">
                        <label for="name" class="item-manage-info__label">商品名</label>
                        <input type="text" id="name" name="name" class="item-manage-info__input" value="{{ old('name', $item->name) }}" required>
                    </div>

                    <div class="item-manage-info__field">
                        <label for="brand_name" class="item-manage-info__label">ブランド名</label>
                        <input type="text" id="brand_name" name="brand_name" class="item-manage-info__input" value="{{ old('brand_name', $item->brand_name) }}">
                    </div>

                    <div class="item-manage-info__field">
                        <label class="item-manage-info__label">販売価格</label>
                        <div class="item-manage-info__price-field">
                            <span class="item-manage-info__price-symbol">¥</span>
                            <input type="number" id="price" name="price" class="item-manage-info__price-input" min="0" value="{{ old('price', $item->price) }}" required>
                        </div>
                    </div>

                    <div class="item-manage-info__field">
                        <label class="item-manage-info__label">いいね数</label>
                        <p class="item-manage-info__text">{{ $item->likes->count() }}</p>
                    </div>

                    <div class="item-manage-info__field">
                        <label class="item-manage-info__label">コメント数</label>
                        <p class="item-manage-info__text">{{ $item->comments->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- 商品説明 -->
            <div class="item-manage-section">
                <h2 class="item-manage-section__title">商品説明</h2>
                <div class="item-manage-section__content">
                    <textarea id="description" name="description" class="item-manage-section__textarea" rows="5" required>{{ old('description', $item->description) }}</textarea>
                </div>
            </div>

            <!-- 商品の情報 -->
            <div class="item-manage-section">
                <h2 class="item-manage-section__title">商品の情報</h2>
                <div class="item-manage-section__content">
                    <div class="item-manage-info-row">
                        <span class="item-manage-info-row__label">カテゴリー</span>
                        <div class="item-manage-info-row__value">
                            <div class="item-manage-info-row__categories">
                                @foreach($categories as $category)
                                <label class="item-manage-info-row__category-item">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="item-manage-info-row__category-checkbox" {{ in_array($category->id, $selectedCategoryIds) ? 'checked' : '' }}>
                                    <span class="item-manage-info-row__category-button">{{ $category->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="item-manage-info-row">
                        <span class="item-manage-info-row__label">商品の状態</span>
                        <select name="condition" class="item-manage-info-row__select" required>
                            <option value="">選択してください</option>
                            <option value="新品" {{ $item->condition === '新品' ? 'selected' : '' }}>新品</option>
                            <option value="未使用に近い" {{ $item->condition === '未使用に近い' ? 'selected' : '' }}>未使用に近い</option>
                            <option value="目立った傷や汚れなし" {{ $item->condition === '目立った傷や汚れなし' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                            <option value="やや傷や汚れあり" {{ $item->condition === 'やや傷や汚れあり' ? 'selected' : '' }}>やや傷や汚れあり</option>
                            <option value="傷や汚れあり" {{ $item->condition === '傷や汚れあり' ? 'selected' : '' }}>傷や汚れあり</option>
                            <option value="全体的に状態が悪い" {{ $item->condition === '全体的に状態が悪い' ? 'selected' : '' }}>全体的に状態が悪い</option>
                        </select>
                    </div>
                    <div class="item-manage-info-row">
                        <span class="item-manage-info-row__label">公開設定</span>
                        <label class="item-manage-info-row__switch">
                            <input type="checkbox" name="is_published" value="1" {{ $item->is_published ? 'checked' : '' }} class="item-manage-info-row__switch-input">
                            <span class="item-manage-info-row__switch-label">商品を公開する</span>
                        </label>
                    </div>
                </div>
            </div>

            @if (session('status'))
            <div class="item-manage-message">
                <p class="item-manage-message__text">{{ session('status') }}</p>
            </div>
            @endif

            <button type="submit" class="item-manage-form__submit">更新する</button>
        </form>
    </div>
</div>

<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'item-manage-image__preview-img';
                img.id = 'imagePreview';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
