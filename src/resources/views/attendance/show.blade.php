@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="attendance-detail-wrapper">

    <h2 class="attendance-detail-title">｜勤怠詳細</h2>

    <form method="POST" action="{{ route('attendance.request',$attendance->id) }}" novalidate>
        @csrf
        @error('attendance')
        <div class="error-message">
            {{ $message }}
        </div>
        @enderror
        <div class="attendance-detail-box">

            {{-- 名前 --}}
            <div class="attendance-row">
                <div class="attendance-label">名前</div>

                <div class="attendance-value">
                    {{ Auth::user()->name }}
                </div>
            </div>

            {{-- 日付 --}}
            <div class="attendance-row">
                <div class="attendance-label">日付</div>

                <div class="attendance-date-group">

                    <span class="attendance-date-year">
                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                    </span>

                    <span class="attendance-date-md">
                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                    </span>

                </div>
            </div>

            {{-- 出勤退勤 --}}
            <div class="attendance-row">

                <div class="attendance-label">出勤・退勤</div>

                <div class="attendance-input-group">

                    <input
                        type="time"
                        name="clock_in"
                        step="60"
                        class="attendance-time-input"
                        value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">

                    <span class="attendance-wave">〜</span>

                    <input
                        type="time"
                        name="clock_out"
                        step="60"
                        class="attendance-time-input"
                        value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">

                </div>

            </div>

            @error('clock_in')
            <div class="error-message">{{ $message }}</div>
            @enderror


            {{-- 休憩 --}}
            <div class="attendance-row">

                <div class="attendance-label">休憩</div>

                <div class="attendance-input-group">

                    <input
                        type="time"
                        name="break_start[]"
                        step="60"
                        class="attendance-time-input"
                        value="{{ old('break_start.0', isset($attendance->breaks[0]) && $attendance->breaks[0]->break_start ? \Carbon\Carbon::parse($attendance->breaks[0]->break_start)->format('H:i') : '') }}">

                    <span class="attendance-wave">〜</span>

                    <input
                        type="time"
                        name="break_end[]"
                        step="60"
                        class="attendance-time-input"
                        value="{{ old('break_end.0', isset($attendance->breaks[0]) && $attendance->breaks[0]->break_end ? \Carbon\Carbon::parse($attendance->breaks[0]->break_end)->format('H:i') : '') }}">
                </div>

            </div>

            @error('break_start')
            <div class="error-message">{{ $message }}</div>
            @enderror


            {{-- 休憩2 --}}
            <div class="attendance-row">

                <div class="attendance-label">休憩2</div>

                <div class="attendance-input-group">

                    <input
                        type="time"
                        name="break_start[]"
                        step="60"
                        class="attendance-time-input"
                        value="{{ old('break_start.1', isset($attendance->breaks[1]) && $attendance->breaks[1]->break_start ? \Carbon\Carbon::parse($attendance->breaks[1]->break_start)->format('H:i') : '') }}">

                    <span class="attendance-wave">〜</span>

                    <input
                        type="time"
                        name="break_end[]"
                        step="60"
                        class="attendance-time-input"
                        value="{{ old('break_end.1', isset($attendance->breaks[1]) && $attendance->breaks[1]->break_end ? \Carbon\Carbon::parse($attendance->breaks[1]->break_end)->format('H:i') : '') }}">
                </div>

            </div>


            {{-- 備考 --}}
            <div class="attendance-row">

                <div class="attendance-label">備考</div>

                <div class="attendance-value">

                    <textarea
                        name="note"
                        class="attendance-note">{{ old('note',$attendance->note) }}</textarea>

                </div>

            </div>

            @if(!$errors->has('attendance'))
            @error('note')
            <div class="error-message">{{ $message }}</div>
            @enderror
            @endif

        </div>

        <div class="attendance-submit-area">

            <button type="submit" class="attendance-submit-btn">
                修正
            </button>

        </div>

    </form>

</div>

@endsection