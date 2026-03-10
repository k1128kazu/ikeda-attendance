@extends('layouts.app')

@section('content')
<div class="admin-dashboard-wrapper">
    <h1 class="admin-dashboard-title">管理者ダッシュボード</h1>

    <div class="admin-dashboard-menu">
        <a href="/admin/users" class="admin-dashboard-link">ユーザー一覧</a>
        <a href="/admin/corrections" class="admin-dashboard-link">修正申請一覧</a>
    </div>
</div>
@endsection