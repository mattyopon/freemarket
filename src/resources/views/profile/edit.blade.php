@extends('layouts.app')

@section('title', 'プロフィール設定')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile/edit.css') }}">
@endsection

@section('content')
<div class="profile-edit-page">
    <div class="profile-edit-page__header">
        <div class="profile-edit-page__logo">
            <a href="{{ route('items.index') }}" class="profile-edit-page__logo-link">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="profile-edit-page__logo-img">
            </a>
        </div>
        <div class="profile-edit-page__search">
            <form method="GET" action="{{ route('mypage') }}" class="profile-edit-page__search-form">
                <div class="profile-edit-page__search-wrapper">
                    <input type="text" name="search" class="profile-edit-page__search-input" placeholder="なにをお探しですか?" value="{{ request('search') }}">
                    <button type="submit" class="profile-edit-page__search-button">検索</button>
                </div>
            </form>
        </div>
        <div class="profile-edit-page__nav">
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="profile-edit-page__nav-link-button">ログアウト</button>
            </form>
            <a href="{{ route('mypage') }}" class="profile-edit-page__nav-link">マイページ</a>
            <a href="{{ route('items.sell') }}" class="profile-edit-page__nav-button">出品</a>
        </div>
    </div>

    <div class="profile-edit-container">
        <h1 class="profile-edit-container__title">プロフィール設定</h1>

        <form class="profile-edit-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="profile-edit-form__image-section">
                <div class="profile-edit-form__image-preview">
                    @if($user->profile_image)
                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像" class="profile-edit-form__image-preview-img">
                    @else
                        <div class="profile-edit-form__image-placeholder"></div>
                    @endif
                </div>
                <label for="profile_image" class="profile-edit-form__image-button">
                    画像を選択する
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                </label>
            </div>

            <div class="profile-edit-form__field">
                <label for="name" class="profile-edit-form__label">ユーザー名</label>
                <input type="text" id="name" name="name" class="profile-edit-form__input @error('name') profile-edit-form__input--error @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                <p class="profile-edit-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="profile-edit-form__field">
                <label for="postal_code" class="profile-edit-form__label">郵便番号</label>
                <input type="text" id="postal_code" name="postal_code" class="profile-edit-form__input @error('postal_code') profile-edit-form__input--error @enderror" value="{{ old('postal_code', $user->postal_code) }}">
                @error('postal_code')
                <p class="profile-edit-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="profile-edit-form__field">
                <label for="address" class="profile-edit-form__label">住所</label>
                <input type="text" id="address" name="address" class="profile-edit-form__input @error('address') profile-edit-form__input--error @enderror" value="{{ old('address', $user->address) }}">
                @error('address')
                <p class="profile-edit-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="profile-edit-form__field">
                <label for="building_name" class="profile-edit-form__label">建物名</label>
                <input type="text" id="building_name" name="building_name" class="profile-edit-form__input @error('building_name') profile-edit-form__input--error @enderror" value="{{ old('building_name', $user->building_name) }}">
                @error('building_name')
                <p class="profile-edit-form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="profile-edit-form__submit">更新する</button>

            @if (session('status'))
            <div class="profile-edit-form__success-message">
                <p>{{ session('status') }}</p>
            </div>
            @endif
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.profile-edit-form__image-preview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="プロフィール画像" class="profile-edit-form__image-preview-img">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

