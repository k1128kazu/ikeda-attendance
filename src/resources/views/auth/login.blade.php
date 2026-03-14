@extends('layouts.app')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('title', 'ログイン')

@section('content')
<div class="auth-container">

    <h1 class="auth-title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

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

        <button type="submit" class="auth-button">
            ログイン
        </button>

        <p class="auth-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </p>

    </form>

</div>
@endsection