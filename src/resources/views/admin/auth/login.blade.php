@extends('layouts.app')

@section('content')

<div class="admin-login-wrapper">

    <h1 class="admin-login-title">管理者ログイン</h1>

    <form method="POST" action="/admin/login" novalidate>
        @csrf

        {{-- メール --}}
        <div class="admin-form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}">

            @error('email')
            <div class="admin-field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="admin-form-group">
            <label>パスワード</label>
            <input type="password" name="password">

            @error('password')
            <div class="admin-field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="admin-login-btn">
            管理者ログインする
        </button>

    </form>

</div>

@endsection