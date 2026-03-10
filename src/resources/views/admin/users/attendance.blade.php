@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="page-title">{{ $staff->name }}さんの勤怠</h2>

    <div class="month-nav">
        <a href="?month={{ \Carbon\Carbon::parse($month)->subMonth()->format('Y-m') }}">← 前月</a>

        <span>{{ \Carbon\Carbon::parse($month)->format('Y/m') }}</span>

        <a href="?month={{ \Carbon\Carbon::parse($month)->addMonth()->format('Y-m') }}">翌月 →</a>
    </div>

    <table class="table">
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
            @foreach($days as $day)
            @php
            $key = $day->format('Y-m-d');
            $attendance = $attendances->get($key);

            $breakSec = 0;
            if ($attendance) {
            $breakSec = (int) ($breakSecondsByAttendanceId[$attendance->id] ?? 0);
            }

            $breakTime = $attendance ? gmdate('H:i', $breakSec) : '';

            $workTime = '';
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
            $in = \Carbon\Carbon::parse($attendance->clock_in);
            $out = \Carbon\Carbon::parse($attendance->clock_out);
            $workSec = max(0, $out->diffInSeconds($in) - $breakSec);
            $workTime = gmdate('H:i', $workSec);
            }
            @endphp

            <tr>
                <td>{{ $day->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$day->dayOfWeek] }})</td>

                <td>{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>

                <td>{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>

                <td>{{ $breakTime }}</td>

                <td>{{ $workTime }}</td>

                <td>
                    @if($attendance)
                    <a href="{{ route('admin.attendances.detail', $attendance->id) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align:right;margin-top:20px;">
        <a href="{{ route('admin.staff.csv',$staff->id) }}?month={{ $month }}">
            <button type="button">CSV出力</button>
        </a>
    </div>

</div>

@endsection