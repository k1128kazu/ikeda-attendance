@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="attendance-list-container">

    <h2 class="attendance-list-title">｜勤怠一覧</h2>

    {{-- 月ナビ --}}
    <div class="attendance-month-nav">

        <div class="month-prev">
            <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}">
                ← 前月
            </a>
        </div>

        <div class="month-center">
            <form method="GET" action="{{ route('attendance.list') }}" id="monthForm">

                <span
                    class="calendar-icon"
                    onclick="document.getElementById('monthPicker').showPicker()"
                    style="cursor:pointer;">
                    📅
                </span>

                <span class="month-text">
                    {{ \Carbon\Carbon::parse($month)->format('Y/m') }}
                </span>

                <input
                    id="monthPicker"
                    type="month"
                    name="month"
                    value="{{ $month }}"
                    onchange="document.getElementById('monthForm').submit()"
                    style="position:absolute; opacity:0;">
            </form>
        </div>

        <div class="month-next">
            <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}">
                翌月 →
            </a>
        </div>

    </div>

    {{-- 勤怠テーブル --}}
    <div class="attendance-table-box">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($list as $row)

                @php
                $attendance = $row['attendance'];

                $clockIn = $attendance && $attendance->clock_in
                ? \Carbon\Carbon::parse($attendance->clock_in)
                : null;

                $clockOut = $attendance && $attendance->clock_out
                ? \Carbon\Carbon::parse($attendance->clock_out)
                : null;

                $breakMinutes = $attendance
                ? $attendance->breaks->sum(function ($b) {
                if (!$b->break_end) {
                return 0;
                }

                return \Carbon\Carbon::parse($b->break_end)
                ->diffInMinutes(\Carbon\Carbon::parse($b->break_start));
                })
                : 0;

                $workMinutes = 0;

                if ($clockIn && $clockOut) {
                $workMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;
                }
                @endphp

                <tr>
                    <td>
                        {{ $row['date']->locale('ja')->isoFormat('MM/DD(ddd)') }}
                    </td>

                    <td>
                        {{ $clockIn ? $clockIn->format('H:i') : '' }}
                    </td>

                    <td>
                        {{ $clockOut ? $clockOut->format('H:i') : '' }}
                    </td>

                    <td>
                        {{ $attendance ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '' }}
                    </td>

                    <td>
                        {{ $attendance ? floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) : '' }}
                    </td>

                    <td>
                        @if($attendance)
                        <a href="{{ route('attendance.show', $attendance->id) }}" class="attendance-detail-link">
                            詳細
                        </a>
                        @endif
                    </td>
                </tr>

                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection