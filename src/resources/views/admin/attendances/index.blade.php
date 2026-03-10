@extends('layouts.app')

@section('content')
<div class="admin-att-index">

    <h1 class="admin-att-title">
        <span class="admin-att-title-bar"></span>
        {{ $date->format('Y年n月j日') }}の勤怠
    </h1>

    {{-- ▼ 日付バー --}}
    <div class="admin-att-datebar">

        <a href="{{ route('admin.index', ['date' => $date->copy()->subDay()->toDateString()]) }}"
            class="admin-att-navbtn">
            ← 前日
        </a>

        <div class="admin-att-date">
            <span class="admin-att-cal">📅</span>
            <span>{{ $date->format('Y/m/d') }}</span>
        </div>

        <a href="{{ route('admin.index', ['date' => $date->copy()->addDay()->toDateString()]) }}"
            class="admin-att-navbtn">
            翌日 →
        </a>

    </div>

    {{-- ▼ テーブル --}}
    <div class="admin-att-table-wrap">
        <table class="admin-att-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)

                @php
                $breakMinutes = $attendance->breaks->sum(function($b){
                if ($b->break_start && $b->break_end) {
                return \Carbon\Carbon::parse($b->break_end)
                ->diffInMinutes($b->break_start);
                }
                return 0;
                });

                $workMinutes = 0;

                if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = \Carbon\Carbon::parse($attendance->clock_out)
                ->diffInMinutes($attendance->clock_in)
                - $breakMinutes;
                }
                @endphp

                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                    <td>{{ sprintf('%d:%02d', floor($breakMinutes/60), $breakMinutes%60) }}</td>
                    <td>{{ sprintf('%d:%02d', floor($workMinutes/60), $workMinutes%60) }}</td>
                    <td><a href="{{ route('admin.attendances.detail', $attendance->id) }}">詳細</a>
                    </td>    
                </tr>

                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection