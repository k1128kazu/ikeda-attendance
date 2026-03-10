@extends('layouts.app')

@section('title', '会員登録')

@section('content')
<div class="auth-container">

    <h1 class="auth-title">会員登録</h1>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="form-group">
            <label class="form-label">お名前</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-input">
            @error('name')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-input">
            @error('email')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">パスワード</label>
            <input type="password" name="password" class="form-input">
            @error('password')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">パスワード確認</label>
            <input type="password" name="password_confirmation" class="form-input">
        </div>

        <button type="submit" class="auth-button">
            登録する
        </button>

    </form>

</div>
@endsection