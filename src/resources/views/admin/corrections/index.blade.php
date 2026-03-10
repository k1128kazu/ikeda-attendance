@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="page-title">申請一覧</h2>

    <div class="tab-menu">

        <a href="/admin/corrections?status=pending"
            class="{{ $status=='pending'?'active':'' }}">
            承認待ち
        </a>

        <a href="/admin/corrections?status=approved"
            class="{{ $status=='approved'?'active':'' }}">
            承認済み
        </a>

    </div>

    <table class="table">

        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>

            @foreach($requests as $request)

            <tr>

                <td>
                    {{ $request->status == 'pending' ? '承認待ち' : '承認済み' }}
                </td>

                <td>
                    {{ $request->user->name }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
                </td>

                <td>
                    {{ $request->request_note }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}
                </td>

                <td>

                    <a href="{{ route('admin.corrections.show', $request->id) }}">
                        詳細
                    </a>

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection