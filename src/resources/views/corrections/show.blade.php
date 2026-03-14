@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="attendance-detail-wrapper">

    <h2 class="attendance-detail-title">｜申請詳細</h2>

    <div class="attendance-detail-box">

        <div class="attendance-row">
            <div class="attendance-label">名前</div>
            <div class="attendance-value">
                {{ $request->attendance->user->name }}
            </div>
        </div>

        <div class="attendance-row">
            <div class="attendance-label">日付</div>
            <div class="attendance-value">
                {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}
            </div>
        </div>

        <div class="attendance-row">
            <div class="attendance-label">出勤・退勤</div>

            <div class="attendance-value">
                {{ \Carbon\Carbon::parse($request->request_clock_in)->format('H:i') }}
                ～
                {{ \Carbon\Carbon::parse($request->request_clock_out)->format('H:i') }}
            </div>
        </div>
        <div class="attendance-row">
            <div class="attendance-label">休憩</div>

            <div class="attendance-value">

                @foreach($request->breaks as $break)

                <div>
                    {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                    ～
                    {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
                </div>

                @endforeach

            </div>
        </div>
        <div class="attendance-row">
            <div class="attendance-label">備考</div>

            <div class="attendance-value">
                {{ $request->request_note }}
            </div>
        </div>
    </div>

</div>

@endsection