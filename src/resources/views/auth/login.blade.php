@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="login-page">
    <div class="login-page__header">
        <div class="login-page__logo">
            <a href="{{ route('items.index') }}" class="login-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="login-page__logo-img">
            </a>
        </div>
    </div>

    <div class="login-container">

        <h2 class="login-container__title">ログイン</h2>

        <form class="login-form" method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="login-form__field">
                <label for="email" class="login-form__label">メールアドレス</label>
                <input type="text" id="email" name="email" class="login-form__input @error('email') login-form__input--error @enderror" value="{{ old('email') }}" autofocus>
                @error('email')
                <p class="login-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="login-form__field">
                <label for="password" class="login-form__label">パスワード</label>
                <input type="password" id="password" name="password" class="login-form__input @error('password') login-form__input--error @enderror">
                @error('password')
                <p class="login-form__error">{{ $message }}</p>
                @enderror
            </div>

            @if ($errors->has('login'))
            <div class="login-form__error-message">
                <p>{{ $errors->first('login') }}</p>
            </div>
            @endif

            @if (session('status'))
            <div class="login-form__success-message">
                <p>{{ session('status') }}</p>
            </div>
            @endif

            <button type="submit" class="login-form__submit">ログインする</button>
        </form>

        <div class="login-container__link">
            <a href="{{ route('register') }}" class="login-container__link-text">会員登録はこちら</a>
        </div>
    </div>
</div>
@endsection

