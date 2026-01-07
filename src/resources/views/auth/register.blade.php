@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-page">
    <div class="register-page__header">
        <div class="register-page__logo">
            <a href="{{ route('items.index') }}" class="register-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="register-page__logo-img">
            </a>
        </div>
    </div>

    <div class="register-container">
        <h2 class="register-container__title">会員登録</h2>

        <form class="register-form" method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <div class="register-form__field">
                <label for="name" class="register-form__label">ユーザー名</label>
                <input type="text" id="name" name="name" class="register-form__input @error('name') register-form__input--error @enderror" value="{{ old('name') }}" autofocus>
                @error('name')
                <p class="register-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="register-form__field">
                <label for="email" class="register-form__label">メールアドレス</label>
                <input type="text" id="email" name="email" class="register-form__input @error('email') register-form__input--error @enderror" value="{{ old('email') }}">
                @error('email')
                <p class="register-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="register-form__field">
                <label for="password" class="register-form__label">パスワード</label>
                <input type="password" id="password" name="password" class="register-form__input @error('password') register-form__input--error @enderror">
                @error('password')
                <p class="register-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="register-form__field">
                <label for="password_confirmation" class="register-form__label">確認用パスワード</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="register-form__input @error('password_confirmation') register-form__input--error @enderror">
                @error('password_confirmation')
                <p class="register-form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="register-form__submit">登録する</button>
        </form>

        <div class="register-container__link">
            <a href="{{ route('login') }}" class="register-container__link-text">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection

