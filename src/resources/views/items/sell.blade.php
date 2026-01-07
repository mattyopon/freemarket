@extends('layouts.app')

@section('title', '商品の出品')

@section('css')
<link rel="stylesheet" href="{{ asset('css/items/sell.css') }}">
@endsection

@section('content')
<div class="item-sell-page">
    <div class="item-sell-page__header">
        <div class="item-sell-page__logo">
            <a href="{{ route('items.index') }}" class="item-sell-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="item-sell-page__logo-img">
            </a>
        </div>
        <div class="item-sell-page__search">
            <form method="GET" action="{{ route('mypage') }}" class="item-sell-page__search-form">
                <div class="item-sell-page__search-wrapper">
                    <input type="text" name="search" class="item-sell-page__search-input" placeholder="なにをお探しですか?" value="{{ request('search') }}">
                    <button type="submit" class="item-sell-page__search-button">検索</button>
                </div>
            </form>
        </div>
        <div class="item-sell-page__nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="item-sell-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="item-sell-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="item-sell-page__nav-button">出品</a>
            @else
            <a href="{{ route('login') }}" class="item-sell-page__nav-link">ログイン</a>
            @endauth
        </div>
    </div>

    <div class="item-sell-container">
        <h1 class="item-sell-container__title">商品の出品</h1>
        <form class="item-sell-form" method="POST" action="{{ route('items.sell.store') }}" enctype="multipart/form-data">
            @csrf
            <!-- 商品画像 -->
            <div class="item-sell-form__section">
                <label class="item-sell-form__label">商品画像</label>
                <div class="item-sell-form__image-area">
                    <label for="item_image" class="item-sell-form__image-button">
                        画像を選択する
                        <input type="file" id="item_image" name="item_image[]" accept="image/*" multiple style="display: none;" onchange="previewImages(this)">
                    </label>
                    <div class="item-sell-form__image-preview" id="imagePreview"></div>
                </div>
            </div>

            <!-- 商品の詳細 -->
            <div class="item-sell-form__section">
                <label class="item-sell-form__label">商品の詳細</label>
                
                <!-- カテゴリー -->
                <div class="item-sell-form__subsection">
                    <label class="item-sell-form__sub-label">カテゴリー</label>
                    <div class="item-sell-form__categories">
                        @foreach($categories as $category)
                        <label class="item-sell-form__category-item">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="item-sell-form__category-checkbox">
                            <span class="item-sell-form__category-button">{{ $category->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- 商品の状態 -->
                <div class="item-sell-form__subsection">
                    <label class="item-sell-form__sub-label">商品の状態</label>
                    <select name="condition" class="item-sell-form__select" required>
                        <option value="">選択してください</option>
                        <option value="新品">新品</option>
                        <option value="未使用に近い">未使用に近い</option>
                        <option value="目立った傷や汚れなし">目立った傷や汚れなし</option>
                        <option value="やや傷や汚れあり">やや傷や汚れあり</option>
                        <option value="傷や汚れあり">傷や汚れあり</option>
                        <option value="全体的に状態が悪い">全体的に状態が悪い</option>
                    </select>
                </div>
            </div>

            <!-- 商品名と説明 -->
            <div class="item-sell-form__section">
                <label class="item-sell-form__label">商品名と説明</label>
                
                <div class="item-sell-form__field">
                    <label for="name" class="item-sell-form__sub-label">商品名</label>
                    <input type="text" id="name" name="name" class="item-sell-form__input" required>
                </div>

                <div class="item-sell-form__field">
                    <label for="brand_name" class="item-sell-form__sub-label">ブランド名</label>
                    <input type="text" id="brand_name" name="brand_name" class="item-sell-form__input">
                </div>

                <div class="item-sell-form__field">
                    <label for="description" class="item-sell-form__sub-label">商品の説明</label>
                    <textarea id="description" name="description" class="item-sell-form__textarea" rows="5" required></textarea>
                </div>
            </div>

            <!-- 販売価格 -->
            <div class="item-sell-form__section">
                <label class="item-sell-form__label">販売価格</label>
                <div class="item-sell-form__price-field">
                    <span class="item-sell-form__price-symbol">¥</span>
                    <input type="number" id="price" name="price" class="item-sell-form__price-input" min="0" required>
                </div>
            </div>

            <button type="submit" class="item-sell-form__submit">出品する</button>
        </form>
    </div>
</div>

<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'item-sell-form__preview-img';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}
</script>
@endsection

