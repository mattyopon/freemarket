@extends('layouts.app')

@section('title', '購入手続き')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase/create.css') }}">
@endsection

@section('content')
<div class="purchase-page">
    <div class="purchase-page__header">
        <div class="purchase-page__logo">
            <a href="{{ route('items.index') }}" class="purchase-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="purchase-page__logo-img">
            </a>
        </div>
        <div class="purchase-page__search">
            <form method="GET" action="{{ Auth::check() ? route('mypage') : route('items.index') }}" class="purchase-page__search-form">
                <div class="purchase-page__search-wrapper">
                    <input type="text" name="search" class="purchase-page__search-input" placeholder="なにをお探しですか?" value="{{ request('search') }}">
                    <button type="submit" class="purchase-page__search-button">検索</button>
                </div>
            </form>
        </div>
        <div class="purchase-page__nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="purchase-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="purchase-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="purchase-page__nav-button">出品</a>
            @else
            <a href="{{ route('login') }}" class="purchase-page__nav-link">ログイン</a>
            @endauth
        </div>
    </div>

    <div class="purchase-container">
        @if (session('status'))
        <div class="purchase-message">
            <p class="purchase-message__text">{{ session('status') }}</p>
        </div>
        @endif

        <div class="purchase-main">
            <!-- 左カラム -->
            <div class="purchase-left">
                <!-- 商品情報 -->
                <div class="purchase-product">
                    <div class="purchase-product__image">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="purchase-product__img">
                        @else
                            <div class="purchase-product__placeholder">商品画像</div>
                        @endif
                    </div>
                    <div class="purchase-product__info">
                        <h2 class="purchase-product__name">{{ $item->name }}</h2>
                        <p class="purchase-product__price">¥ {{ number_format($item->price) }}</p>
                    </div>
                </div>

                <hr class="purchase-divider">

                <!-- 支払い方法 -->
                <div class="purchase-section">
                    <h3 class="purchase-section__title">支払い方法</h3>
                    <form method="POST" action="{{ route('purchase.store', ['item' => $item->id]) }}" id="purchaseForm">
                        @csrf
                        <div class="purchase-payment-select-wrapper">
                            <select name="payment_method" id="payment_method" class="purchase-payment-select" required>
                                <option value="コンビニ払い" {{ old('payment_method', 'コンビニ払い') === 'コンビニ払い' ? 'selected' : '' }}>コンビニ払い</option>
                                <option value="カード支払い" {{ old('payment_method') === 'カード支払い' ? 'selected' : '' }}>カード支払い</option>
                            </select>
                        </div>
                        @error('payment_method')
                        <p class="purchase-section__error">{{ $message }}</p>
                        @enderror
                    </form>
                </div>

                <hr class="purchase-divider">

                <!-- 配送先 -->
                <div class="purchase-section">
                    <div class="purchase-section__header">
                        <h3 class="purchase-section__title">配送先</h3>
                        <a href="{{ route('purchase.address.edit', ['item' => $item->id]) }}" class="purchase-section__change-link">変更する</a>
                    </div>
                    <div class="purchase-section__address">
                        @if(isset($shippingAddress) && $shippingAddress)
                            @php
                                // セッションに保存された配送先住所を表示
                                $addressParts = explode(' ', $shippingAddress, 2);
                                $postalCode = str_replace('〒', '', $addressParts[0] ?? '');
                                $address = $addressParts[1] ?? '';
                            @endphp
                            <p class="purchase-section__address-line">
                                @if($postalCode)
                                    〒 {{ $postalCode }}
                                @else
                                    〒 XXX-YYYY
                                @endif
                            </p>
                            <p class="purchase-section__address-line">
                                @if($address)
                                    {{ $address }}
                                @else
                                    ここには住所と建物が入ります
                                @endif
                            </p>
                        @elseif($user->postal_code || $user->address || $user->building_name)
                            <p class="purchase-section__address-line">
                                @if($user->postal_code)
                                    〒 {{ $user->postal_code }}
                                @else
                                    〒 XXX-YYYY
                                @endif
                            </p>
                            <p class="purchase-section__address-line">
                                @if($user->address || $user->building_name)
                                    {{ $user->address }}{{ $user->building_name ? ' ' . $user->building_name : '' }}
                                @else
                                    ここには住所と建物が入ります
                                @endif
                            </p>
                        @else
                            <p class="purchase-section__address-line">〒 XXX-YYYY</p>
                            <p class="purchase-section__address-line">ここには住所と建物が入ります</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 右カラム -->
            <div class="purchase-right">
                <div class="purchase-summary">
                    <div class="purchase-summary__row">
                        <span class="purchase-summary__label">商品代金</span>
                        <span class="purchase-summary__value">¥ {{ number_format($item->price) }}</span>
                    </div>
                    <div class="purchase-summary__row">
                        <span class="purchase-summary__label">支払い方法</span>
                        <span class="purchase-summary__value" id="summary_payment_method">-</span>
                    </div>
                </div>
                
                @if(isset($sessionId) && $paymentStatus === 'paid')
                    <div class="purchase-status-message purchase-status-message--success">
                        <p class="purchase-status-message__text">決済が完了しています</p>
                        <a href="{{ route('purchase.stripe.success', ['item' => $item->id]) }}?session_id={{ $sessionId }}&popup=false" class="purchase-status-message__link">購入完了画面へ</a>
                    </div>
                @elseif(isset($sessionId) && $paymentStatus === 'unpaid')
                    <div class="purchase-status-message purchase-status-message--pending">
                        <p class="purchase-status-message__text">決済がまだ完了していません。Stripe Dashboardで決済を完了させた後、下のボタンをクリックしてください。</p>
                        <a href="{{ route('purchase.stripe.success', ['item' => $item->id]) }}?session_id={{ $sessionId }}&popup=false" class="purchase-status-message__link">決済状態を確認</a>
                    </div>
                @endif
                
                <button type="submit" form="purchaseForm" class="purchase-button" id="purchaseButton">購入する</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method');
    const summaryPaymentMethod = document.getElementById('summary_payment_method');
    const purchaseForm = document.getElementById('purchaseForm');
    const purchaseButton = document.getElementById('purchaseButton');
    
    function updateSummary() {
        if (paymentMethodSelect && paymentMethodSelect.value) {
            summaryPaymentMethod.textContent = paymentMethodSelect.value;
        } else {
            summaryPaymentMethod.textContent = '-';
        }
    }
    
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            updateSummary();
        });
    }
    
    // 初期値の設定
    updateSummary();
    
    // フォーム送信時にStripe決済の場合は別ウィンドウで開く
    if (purchaseForm && purchaseButton) {
        purchaseForm.addEventListener('submit', function(e) {
            const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : '';
            
            // コンビニ支払いまたはカード支払いの場合は別ウィンドウで開く
            if (paymentMethod === 'コンビニ払い' || paymentMethod === 'カード支払い') {
                e.preventDefault();
                
                // フォームデータを取得
                const formData = new FormData(purchaseForm);
                
                // AJAXでStripeのURLを取得
                fetch(purchaseForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    return response.json().then(data => {
                        throw new Error(data.error || '決済画面へのリダイレクトに失敗しました');
                    });
                })
                .then(data => {
                    const checkoutUrl = data.checkout_url;
                    const sessionId = data.session_id;
                    
                    // セッションIDをローカルストレージに保存（後で確認できるように）
                    if (sessionId) {
                        localStorage.setItem('stripe_checkout_session_id_{{ $item->id }}', sessionId);
                    }
                    
                    // 別ウィンドウでStripe決済画面を開く
                    const stripeWindow = window.open(checkoutUrl, 'stripe-checkout', 'width=600,height=700,scrollbars=yes,resizable=yes');
                    
                    if (!stripeWindow) {
                        alert('ポップアップがブロックされました。ポップアップを許可してから再度お試しください。');
                        return;
                    }
                    
                    // ウィンドウが閉じられたか、決済が完了したかを監視
                    const checkWindow = setInterval(function() {
                        if (stripeWindow.closed) {
                            clearInterval(checkWindow);
                            // ウィンドウが閉じられた場合、ページをリロードして状態を確認
                            window.location.reload();
                        }
                    }, 1000);
                    
                    // メッセージリスナーで決済完了を検知
                    const messageHandler = function(event) {
                        if (event.data && event.data.type === 'stripe-checkout-complete') {
                            clearInterval(checkWindow);
                            window.removeEventListener('message', messageHandler);
                            // 決済完了画面にリダイレクト
                            window.location.href = event.data.redirectUrl;
                        }
                    };
                    window.addEventListener('message', messageHandler);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('決済画面を開く際にエラーが発生しました: ' + error.message);
                });
            }
            // その他の支払い方法の場合は通常通り送信
        });
    }
    
    // キャンセルされた場合の処理
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('canceled') === 'true') {
        // キャンセルメッセージを表示（必要に応じて）
        console.log('決済がキャンセルされました');
    }
});
</script>
@endsection

