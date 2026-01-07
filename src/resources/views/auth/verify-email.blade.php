@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email-page">
    <div class="verify-email-page__header">
        <div class="verify-email-page__logo">
            <a href="{{ route('items.index') }}" class="verify-email-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="verify-email-page__logo-img">
            </a>
        </div>
    </div>

    <div class="verify-email-container">
        <div class="verify-email-message">
            <p class="verify-email-message__text">登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p class="verify-email-message__text">メール認証を完了してください。</p>
        </div>

        <div class="verify-email-actions">
            <p class="verify-email-message__text">メール内の「Verify Email Address」ボタンをクリックするか、メール内のURLをコピーしてブラウザで開いてください。</p>
        </div>

        <div class="verify-email-resend">
            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="verify-email-resend__link">認証メールを再送する</button>
            </form>
        </div>
    </div>
</div>
@endsection

