<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理システム')</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body>

    <header class="app-header">
        <div class="app-header-inner">

            <div class="app-logo">
                <img src="{{ asset('image/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH">
            </div>

            @auth
            <nav class="app-nav">
                @if(auth()->user()->role === 'admin')

                <a href="/admin">勤怠一覧</a>
                <a href="/admin/users">スタッフ一覧</a>
                <a href="/admin/corrections">申請一覧</a>

                <a href="#"
                    onclick="event.preventDefault(); document.getElementById('admin-logout').submit();">
                    ログアウト
                </a>

                <form id="admin-logout" method="POST" action="/admin/logout" style="display:none;">
                    @csrf
                </form>

                @else

                <a href="/attendance">勤怠</a>
                <a href="/attendance/list">勤怠一覧</a>
                <a href="/corrections">申請一覧</a>

                <a href="#"
                    onclick="event.preventDefault(); document.getElementById('user-logout').submit();">
                    ログアウト
                </a>

                <form id="user-logout" method="POST" action="{{ route('logout') }}" style="display:none;">
                    @csrf
                </form>

                @endif
            </nav>
            @endauth

        </div>
    </header>

    <main>
        @yield('content')
    </main>

</body>

</html>