@extends('layouts.app')

@section('title', '商品登録完了')

@section('css')
<link rel="stylesheet" href="{{ asset('css/items/sell-complete.css') }}">
@endsection

@section('content')
<div class="item-sell-complete-page">
    <div class="item-sell-complete-page__header">
        <div class="item-sell-complete-page__logo">
            <a href="{{ route('items.index') }}" class="item-sell-complete-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="item-sell-complete-page__logo-img">
            </a>
        </div>
        <div class="item-sell-complete-page__search">
            <form method="GET" action="{{ route('mypage') }}" class="item-sell-complete-page__search-form">
                <div class="item-sell-complete-page__search-wrapper">
                    <input type="text" name="search" class="item-sell-complete-page__search-input" placeholder="なにをお探しですか?" value="{{ request('search') }}">
                    <button type="submit" class="item-sell-complete-page__search-button">検索</button>
                </div>
            </form>
        </div>
        <div class="item-sell-complete-page__nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="item-sell-complete-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="item-sell-complete-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="item-sell-complete-page__nav-button">出品</a>
            @else
            <a href="{{ route('login') }}" class="item-sell-complete-page__nav-link">ログイン</a>
            @endauth
        </div>
    </div>

    <div class="item-sell-complete-container">
        <div class="item-sell-complete-message">
            <h1 class="item-sell-complete-message__title">商品の出品が完了しました</h1>
            <p class="item-sell-complete-message__text">ご出品ありがとうございます。</p>
        </div>
        <div class="item-sell-complete-actions">
            <a href="{{ route('items.show', ['item' => $item->id]) }}" class="item-sell-complete-actions__button">出品を確認する</a>
            <a href="{{ route('items.index') }}" class="item-sell-complete-actions__link">商品一覧に戻る</a>
        </div>
    </div>
</div>
@endsection

