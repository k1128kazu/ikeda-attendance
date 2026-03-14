@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('title', 'メール認証')

@section('content')

<div class="auth-container">

    <h1 class="auth-title">メール認証誘導画面</h1>

    <div class="verify-box">

        <p class="verify-text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <div class="verify-button-area">
            <a href="http://localhost:8025" class="verify-button" target="_blank">
                認証はこちらから
            </a>
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="resend-link">
                認証メールを再送する
            </button>
        </form>

    </div>

</div>

@endsection