@extends('layouts.app')

@section('content')
<div class="admin-approve-page">

    <h2 class="admin-approve-title">勤怠詳細</h2>

    <div class="admin-approve-card">

        {{-- 名前 --}}
        <div class="admin-approve-row admin-approve-row-3col">
            <div class="admin-approve-label">名前</div>
            <div class="admin-approve-left">
                {{ $attendance->user->name }}
            </div>
        </div>

        {{-- 日付 --}}
        <div class="admin-approve-row admin-approve-row-3col">
            <div class="admin-approve-label">日付</div>

            <div class="admin-approve-left">
                {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
            </div>

            <div class="admin-approve-right">
                {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="admin-approve-row admin-approve-row-3col">
            <div class="admin-approve-label">出勤・退勤</div>

            <div class="admin-approve-left">
                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
            </div>

            <div class="admin-approve-mid">～</div>

            <div class="admin-approve-right">
                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
            </div>
        </div>

        {{-- 休憩（ここが修正ポイント） --}}
        @foreach($breaks as $index => $break)
        <div class="admin-approve-row admin-approve-row-3col">
            <div class="admin-approve-label">
                {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
            </div>

            <div class="admin-approve-left">
                {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
            </div>

            <div class="admin-approve-mid">～</div>

            <div class="admin-approve-right">
                {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
            </div>
        </div>
        @endforeach

        {{-- 備考 --}}
        <div class="admin-approve-row">
            <div class="admin-approve-label">備考</div>
            <div class="admin-approve-note">
                {{ $request->request_note }}
            </div>
        </div>

    </div>

    <div class="admin-approve-actions">

        @if($request->status === 'pending')

        <form method="POST"
            action="{{ route('admin.corrections.approve', $request->id) }}">
            @csrf

            <button type="submit" class="admin-approve-btn">
                承認
            </button>
        </form>

        @else

        <a href="/stamp_correction_request/list" class="admin-approved-btn">
            承認済み
        </a>

        @endif

    </div>
</div>
@endsection