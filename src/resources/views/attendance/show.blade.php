@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="attendance-detail-wrapper">

    <h2 class="attendance-detail-title">｜勤怠詳細</h2>

    <form method="POST" action="{{ route('attendance.request',$attendance->id) }}" novalidate>
        @csrf

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
                    <input type="time" name="clock_in" class="attendance-time-input break-start"
                        value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>

                    <span class="attendance-wave">〜</span>

                    <input type="time" name="clock_out" class="attendance-time-input break-end"
                        value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>
                </div>
            </div>

            {{-- ▼ 既存休憩 --}}
            @foreach($attendance->breaks as $index => $break)
            <div class="attendance-row break-row">
                <div class="attendance-label">休憩{{ $index + 1 }}</div>

                <div class="attendance-input-group">
                    <input type="time" name="break_start[]" class="attendance-time-input break-start"
                        value="{{ old('break_start.' . $index, $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>

                    <span class="attendance-wave">〜</span>

                    <input type="time" name="break_end[]" class="attendance-time-input break-end"
                        value="{{ old('break_end.' . $index, $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>
                </div>
            </div>
            @endforeach

            {{-- ▼ 空行（常に1つ） --}}
            <div id="break-area">
                <div class="attendance-row break-row">
                    <div class="attendance-label">休憩{{ count($attendance->breaks) + 1 }}</div>

                    <div class="attendance-input-group">
                        <input type="time" name="break_start[]" class="attendance-time-input break-start" @if($isPending) disabled @endif>
                        <span class="attendance-wave">〜</span>
                        <input type="time" name="break_end[]" class="attendance-time-input break-end" @if($isPending) disabled @endif>
                    </div>
                </div>
            </div>

            {{-- 備考 --}}
            <div class="attendance-row">
                <div class="attendance-label">備考</div>

                <div class="attendance-value">
                    <textarea name="note" class="attendance-note" @if($isPending) disabled @endif>{{ old('note',$attendance->note) }}</textarea>
                </div>
            </div>

        </div>

        {{-- ボタン --}}
        <div class="attendance-submit-area">
            @if(!$isPending)
            <button type="submit" class="attendance-submit-btn">
                修正
            </button>
            @endif
        </div>

        {{-- メッセージ --}}
        @if($isPending)
        <div style="color:red; text-align:right; margin-top:16px;">
            ※承認待ちのため修正はできません。
        </div>
        @endif

    </form>

</div>

{{-- ▼ 自動追加JS --}}
@if(!$isPending)
<script>
    document.addEventListener('input', function(e) {

        if (!e.target.classList.contains('break-start') &&
            !e.target.classList.contains('break-end')) return;

        const rows = document.querySelectorAll('.break-row');
        const lastRow = rows[rows.length - 1];

        const start = lastRow.querySelector('.break-start').value;
        const end = lastRow.querySelector('.break-end').value;

        if (start && end) {

            const area = document.getElementById('break-area');
            const index = rows.length;

            const row = document.createElement('div');
            row.className = 'attendance-row break-row';

            row.innerHTML = `
            <div class="attendance-label">休憩${index + 1}</div>
            <div class="attendance-input-group">
                <input type="time" name="break_start[]" class="attendance-time-input break-start">
                <span class="attendance-wave">〜</span>
                <input type="time" name="break_end[]" class="attendance-time-input break-end">
            </div>
        `;

            area.appendChild(row);
        }
    });
</script>
@endif

@endsection