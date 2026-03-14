@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="request-page">

    <div class="request-page__inner">
        <h2 class="request-page__title">｜申請一覧</h2>

        <div class="request-page__tabs">
            <a href="{{ route('corrections.index', ['status' => 'pending']) }}"
                class="request-page__tab {{ $status === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>

            <a href="{{ route('corrections.index', ['status' => 'approved']) }}"
                class="request-page__tab {{ $status === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>

        <div class="request-page__table-wrap">
            <table class="request-page__table">
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
                    @forelse($requests as $request)
                    <tr>
                        <td>
                            @if($request->status === 'pending')
                            承認待ち
                            @elseif($request->status === 'approved')
                            承認済み
                            @else
                            却下
                            @endif
                        </td>

                        <td>{{ $request->user->name }}</td>

                        <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>

                        <td>{{ $request->request_note }}</td>

                        <td>{{ $request->created_at->format('Y/m/d') }}</td>

                        <td>
                            <a href="{{ route('corrections.show', $request->id) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">申請データがありません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection