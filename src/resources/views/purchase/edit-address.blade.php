@extends('layouts.app')

@section('title', '住所の変更')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase/edit-address.css') }}">
@endsection

@section('content')
<div class="purchase-address-edit-page">
    <div class="purchase-address-edit-page__header">
        <div class="purchase-address-edit-page__logo">
            <a href="{{ route('items.index') }}" class="purchase-address-edit-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="purchase-address-edit-page__logo-img">
            </a>
        </div>
        <div class="purchase-address-edit-page__search">
            <form method="GET" action="{{ Auth::check() ? route('mypage') : route('items.index') }}" class="purchase-address-edit-page__search-form">
                <div class="purchase-address-edit-page__search-wrapper">
                    <input type="text" name="search" class="purchase-address-edit-page__search-input" placeholder="なにをお探しですか?" value="{{ request('search') }}">
                    <button type="submit" class="purchase-address-edit-page__search-button">検索</button>
                </div>
            </form>
        </div>
        <div class="purchase-address-edit-page__nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="purchase-address-edit-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="purchase-address-edit-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="purchase-address-edit-page__nav-button">出品</a>
            @else
            <a href="{{ route('login') }}" class="purchase-address-edit-page__nav-link">ログイン</a>
            @endauth
        </div>
    </div>

    <div class="purchase-address-edit-container">
        <h1 class="purchase-address-edit-container__title">住所の変更</h1>
        
        <form method="POST" action="{{ route('purchase.address.update', ['item' => $item->id]) }}" class="purchase-address-edit-form">
            @csrf
            <div class="purchase-address-edit-form__field">
                <label for="postal_code" class="purchase-address-edit-form__label">郵便番号</label>
                <div class="purchase-address-edit-form__input-wrapper">
                    <input type="text" id="postal_code" name="postal_code" class="purchase-address-edit-form__input" value="{{ old('postal_code', $user->postal_code) }}" placeholder="XXX-YYYY">
                    @error('postal_code')
                    <p class="purchase-address-edit-form__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="purchase-address-edit-form__field">
                <label for="address" class="purchase-address-edit-form__label">住所</label>
                <div class="purchase-address-edit-form__input-wrapper">
                    <input type="text" id="address" name="address" class="purchase-address-edit-form__input" value="{{ old('address', $user->address) }}" placeholder="都道府県市区町村番地">
                    @error('address')
                    <p class="purchase-address-edit-form__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="purchase-address-edit-form__field">
                <label for="building_name" class="purchase-address-edit-form__label">建物名</label>
                <div class="purchase-address-edit-form__input-wrapper">
                    <input type="text" id="building_name" name="building_name" class="purchase-address-edit-form__input" value="{{ old('building_name', $user->building_name) }}" placeholder="建物名・部屋番号">
                    @error('building_name')
                    <p class="purchase-address-edit-form__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="purchase-address-edit-form__submit">更新する</button>
        </form>

        @if (session('status'))
        <div class="purchase-address-edit-message">
            <p class="purchase-address-edit-message__text">{{ session('status') }}</p>
            <a href="{{ route('purchase.create', ['item' => $item->id]) }}" class="purchase-address-edit-message__link">商品購入画面に戻る</a>
        </div>
        @endif
    </div>
</div>
@endsection

