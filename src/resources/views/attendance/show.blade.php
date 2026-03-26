@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

@php
use Carbon\Carbon;

$isBeforeClockOut = is_null($attendance->clock_out);

$workDate = Carbon::parse($attendance->work_date);
$clockInValue = old('clock_in', $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '');
$clockOutValue = old('clock_out', $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '');
@endphp

<div class="attendance-detail-page">
    <div class="attendance-detail-wrapper">
        <h2 class="attendance-detail-title">｜勤怠詳細</h2>

        <form method="POST" action="{{ route('attendance.request', $attendance->id) }}" novalidate>
            @csrf

            <div class="attendance-detail-box">

                {{-- 名前 --}}
                <div class="attendance-detail-row attendance-detail-row-name">
                    <div class="attendance-detail-label">名前</div>
                    <div class="attendance-detail-single">
                        {{ Auth::user()->name }}
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="attendance-detail-row attendance-detail-row-date">
                    <div class="attendance-detail-label">日付</div>

                    <div class="attendance-detail-left-text">
                        {{ $workDate->format('Y年') }}
                    </div>

                    <div class="attendance-detail-separator"></div>

                    <div class="attendance-detail-right-text">
                        {{ $workDate->format('n月j日') }}
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">出勤・退勤</div>

                    <div class="attendance-detail-left">
                        <input
                            type="time"
                            name="clock_in"
                            class="attendance-detail-time-input"
                            value="{{ $clockInValue }}"
                            @if($isPending || $isBeforeClockOut) disabled @endif>
                    </div>

                    <div class="attendance-detail-separator">〜</div>

                    <div class="attendance-detail-right">
                        <input
                            type="time"
                            name="clock_out"
                            class="attendance-detail-time-input"
                            value="{{ $clockOutValue }}"
                            @if($isPending || $isBeforeClockOut) disabled @endif>
                    </div>
                </div>

                {{-- 既存休憩 --}}
                @foreach($attendance->breaks as $index => $break)
                @php
                $breakStartValue = old('break_start.' . $index, $break->break_start ? Carbon::parse($break->break_start)->format('H:i') : '');
                $breakEndValue = old('break_end.' . $index, $break->break_end ? Carbon::parse($break->break_end)->format('H:i') : '');
                @endphp

                <div class="attendance-detail-row break-row">
                    <div class="attendance-detail-label">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </div>

                    <div class="attendance-detail-left">
                        <input
                            type="time"
                            name="break_start[]"
                            class="attendance-detail-time-input break-start"
                            value="{{ $breakStartValue }}"
                            @if($isPending || $isBeforeClockOut) disabled @endif>
                    </div>

                    <div class="attendance-detail-separator">〜</div>

                    <div class="attendance-detail-right">
                        <input
                            type="time"
                            name="break_end[]"
                            class="attendance-detail-time-input break-end"
                            value="{{ $breakEndValue }}"
                            @if($isPending || $isBeforeClockOut) disabled @endif>
                    </div>
                </div>
                @endforeach

                {{-- 追加用空行 --}}
                <div id="break-area">
                    <div class="attendance-detail-row break-row">
                        <div class="attendance-detail-label">
                            {{ count($attendance->breaks) === 0 ? '休憩' : '休憩' . (count($attendance->breaks) + 1) }}
                        </div>

                        <div class="attendance-detail-left">
                            <input
                                type="time"
                                name="break_start[]"
                                class="attendance-detail-time-input break-start"
                                @if($isPending || $isBeforeClockOut) disabled @endif>
                        </div>

                        <div class="attendance-detail-separator">〜</div>

                        <div class="attendance-detail-right">
                            <input
                                type="time"
                                name="break_end[]"
                                class="attendance-detail-time-input break-end"
                                @if($isPending || $isBeforeClockOut) disabled @endif>
                        </div>
                    </div>
                </div>

                {{-- 備考 --}}
                <div class="attendance-detail-row attendance-detail-row-note">
                    <div class="attendance-detail-label">備考</div>

                    <div class="attendance-detail-note-wrap">
                        <textarea
                            name="note"
                            class="attendance-detail-note"
                            @if($isPending || $isBeforeClockOut) disabled @endif>{{ old('note', $attendance->note) }}</textarea>
                    </div>
                </div>

            </div>

            @if ($errors->any())
            <div class="attendance-detail-error">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="attendance-detail-submit-area">
                @if(!$isPending && !$isBeforeClockOut)
                <button type="submit" class="attendance-detail-submit-btn">修正</button>
                @endif
            </div>

            @if($isPending)
            <div class="attendance-detail-message">
                ※承認待ちのため修正はできません。
            </div>
            @endif

            @if($isBeforeClockOut)
            <div class="attendance-detail-message">
                ※退勤前のデータは修正できません。
            </div>
            @endif
        </form>
    </div>
</div>

@if(!$isPending && !$isBeforeClockOut)
<script>
    document.addEventListener('input', function(e) {
        if (!e.target.classList.contains('break-start') &&
            !e.target.classList.contains('break-end')) {
            return;
        }

        const rows = document.querySelectorAll('.break-row');
        const lastRow = rows[rows.length - 1];

        const start = lastRow.querySelector('.break-start').value;
        const end = lastRow.querySelector('.break-end').value;

        if (start && end) {
            const area = document.getElementById('break-area');
            const index = rows.length;

            const labelText = index === 1 ? '休憩2' : `休憩${index + 1}`;

            const row = document.createElement('div');
            row.className = 'attendance-detail-row break-row';

            row.innerHTML = `
            <div class="attendance-detail-label">${labelText}</div>
            <div class="attendance-detail-left">
                <input type="time" name="break_start[]" class="attendance-detail-time-input break-start">
            </div>
            <div class="attendance-detail-separator">〜</div>
            <div class="attendance-detail-right">
                <input type="time" name="break_end[]" class="attendance-detail-time-input break-end">
            </div>
        `;

            area.appendChild(row);
        }
    });
</script>
@endif

@endsection