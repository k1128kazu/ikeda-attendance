@extends('layouts.app')

@section('content')

<div class="admin-container">

    <h2 class="page-title">勤怠詳細</h2>

    <form method="POST"
        action="{{ route('admin.attendances.update', $attendance->id) }}"
        novalidate>
        @csrf

        <input type="hidden" name="from" value="{{ request('from') }}">

        <div class="admin-detail-card">

            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">
                    {{ $attendance->user->name }}
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">日付</div>

                <div class="detail-value date-pair">

                    <div class="date-left">
                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                    </div>

                    <div class="date-mid"></div>

                    <div class="date-right">
                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                    </div>

                </div>
            </div>

            <div class="detail-row">

                <div class="detail-label">出勤・退勤</div>

                <div class="detail-value time-pair">

                    <input
                        type="time"
                        name="clock_in"
                        class="attendance-time-input"
                        value="{{ old('clock_in', $clockIn ? $clockIn->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>

                    <span>〜</span>

                    <input
                        type="time"
                        name="clock_out"
                        class="attendance-time-input"
                        value="{{ old('clock_out', $clockOut ? $clockOut->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>

                </div>

            </div>

            @foreach($attendance->breaks as $index => $break)
            <div class="detail-row break-row">

                <div class="detail-label">休憩{{ $index + 1 }}</div>

                <div class="detail-value time-pair">

                    <input
                        type="time"
                        name="break_start[]"
                        class="attendance-time-input break-start"
                        value="{{ old('break_start.' . $index, ($break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '')) }}"
                        @if($isPending) disabled @endif>

                    <span>〜</span>

                    <input
                        type="time"
                        name="break_end[]"
                        class="attendance-time-input break-end"
                        value="{{ old('break_end.' . $index, ($break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '')) }}"
                        @if($isPending) disabled @endif>

                </div>

            </div>
            @endforeach

            <div id="break-area">
                <div class="detail-row break-row">

                    <div class="detail-label">休憩{{ count($attendance->breaks) + 1 }}</div>

                    <div class="detail-value time-pair">

                        <input
                            type="time"
                            name="break_start[]"
                            class="attendance-time-input break-start"
                            @if($isPending) disabled @endif>

                        <span>〜</span>

                        <input
                            type="time"
                            name="break_end[]"
                            class="attendance-time-input break-end"
                            @if($isPending) disabled @endif>

                    </div>

                </div>
            </div>

            <div class="detail-row">

                <div class="detail-label">備考</div>

                <div class="detail-value">

                    <textarea
                        name="note"
                        class="attendance-note"
                        @if($isPending) disabled @endif>{{ old('note', $attendance->note ?? '') }}</textarea>

                </div>

            </div>

        </div>

        <div class="detail-button-area">

            @if(!$isPending)
            <button
                type="submit"
                class="detail-edit-button">
                修正
            </button>
            @endif

        </div>

        @if($isPending)
        <div class="error-message" style="color:#ff6b6b; text-align:right; margin-top:20px;">
            ※承認待ちのため修正はできません。
        </div>
        @endif

    </form>

</div>

@if(!$isPending)
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

            const row = document.createElement('div');
            row.className = 'detail-row break-row';

            row.innerHTML = `
            <div class="detail-label">休憩${index + 1}</div>
            <div class="detail-value time-pair">
                <input type="time" name="break_start[]" class="attendance-time-input break-start">
                <span>〜</span>
                <input type="time" name="break_end[]" class="attendance-time-input break-end">
            </div>
        `;

            area.appendChild(row);
        }
    });
</script>
@endif

@endsection