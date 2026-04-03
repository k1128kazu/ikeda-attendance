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

            {{-- 名前 --}}
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">
                    {{ $attendance->user->name }}
                </div>
            </div>

            {{-- 日付 --}}
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

            {{-- 出勤・退勤 --}}
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

            {{-- 出勤退勤エラー --}}
            @if ($errors->has('clock_in') || $errors->has('clock_out'))
            <div style="color:red; margin-left:200px;">
                {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}
            </div>
            @endif

            {{-- 休憩 --}}
            @php
            $oldBreakStarts = old('break_start', []);
            $oldBreakEnds = old('break_end', []);
            $existingBreakCount = $attendance->breaks->count();
            $oldBreakCount = max(count($oldBreakStarts), count($oldBreakEnds));
            $renderUntil = max($existingBreakCount + 1, $oldBreakCount);
            @endphp

            <div id="break-area">
                @for ($i = 0; $i < $renderUntil; $i++)

                    @php
                    $break=$attendance->breaks[$i] ?? null;

                    $startVal = $oldBreakStarts[$i]
                    ?? ($break && $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '');

                    $endVal = $oldBreakEnds[$i]
                    ?? ($break && $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '');
                    @endphp

                    <div class="detail-row break-row">
                        <div class="detail-label">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</div>

                        <div class="detail-value time-pair">
                            <input
                                type="time"
                                name="break_start[{{ $i }}]"
                                class="attendance-time-input break-start"
                                value="{{ $startVal }}"
                                @if($isPending) disabled @endif>

                            <span>〜</span>

                            <input
                                type="time"
                                name="break_end[{{ $i }}]"
                                class="attendance-time-input break-end"
                                value="{{ $endVal }}"
                                @if($isPending) disabled @endif>
                        </div>
                    </div>

                    {{-- 休憩エラー --}}
                    @if ($errors->has('break_start.' . $i) || $errors->has('break_end.' . $i))
                    <div style="color:red; margin-left:200px;">
                        {{ $errors->first('break_start.' . $i) ?: $errors->first('break_end.' . $i) }}
                    </div>
                    @endif

                    @endfor
            </div>

            {{-- 備考 --}}
            <div class="detail-row">
                <div class="detail-label">備考</div>

                <div class="detail-value">
                    <textarea
                        name="note"
                        class="attendance-note"
                        @if($isPending) disabled @endif>{{ old('note', $attendance->note ?? '') }}</textarea>
                </div>
            </div>

            {{-- 備考エラー --}}
            @if ($errors->has('note'))
            <div style="color:red; margin-left:200px;">
                {{ $errors->first('note') }}
            </div>
            @endif

        </div>

        <div class="detail-button-area">
            @if(!$isPending)
            <button type="submit" class="detail-edit-button">修正</button>
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

        const rows = document.querySelectorAll('#break-area .break-row');
        const lastRow = rows[rows.length - 1];

        const start = lastRow.querySelector('.break-start').value;
        const end = lastRow.querySelector('.break-end').value;

        if (start && end) {
            const area = document.getElementById('break-area');
            const index = rows.length;

            const row = document.createElement('div');
            row.className = 'detail-row break-row';

            row.innerHTML = `
            <div class="detail-label">${index === 0 ? '休憩' : '休憩' + (index + 1)}</div>
            <div class="detail-value time-pair">
                <input type="time" name="break_start[${index}]" class="attendance-time-input break-start">
                <span>〜</span>
                <input type="time" name="break_end[${index}]" class="attendance-time-input break-end">
            </div>
        `;

            area.appendChild(row);
        }
    });
</script>
@endif

@endsection