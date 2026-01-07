@extends('layouts.app')

@section('title', '購入完了')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase/complete.css') }}">
@endsection

@section('content')
<div class="purchase-complete-page">
    <div class="purchase-complete-page__header">
        <div class="purchase-complete-page__logo">
            <a href="{{ route('items.index') }}" class="purchase-complete-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="purchase-complete-page__logo-img">
            </a>
        </div>
    </div>

    <div class="purchase-complete-container">
        <h1 class="purchase-complete-container__title">購入が完了しました</h1>
        <div class="purchase-complete-actions">
            <a href="{{ route('items.index') }}" class="purchase-complete-actions__button">商品一覧に戻る</a>
        </div>
    </div>
</div>
@endsection

