@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="page-title">スタッフ一覧</h2>

    <table class="table">

        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>

        <tbody>

            @foreach($staff as $user)

            <tr>

                <td>{{ $user->name }}</td>

                <td>{{ $user->email }}</td>

                <td>
                    <a href="{{ route('admin.staff.attendance',$user->id) }}">
                        詳細
                    </a>
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection