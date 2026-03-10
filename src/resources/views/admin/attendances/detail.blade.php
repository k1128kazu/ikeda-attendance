@extends('layouts.app')

@section('content')

<div style="max-width: 960px; margin: 0 auto; padding: 40px 20px;">

    <h2 class="page-title">勤怠詳細</h2>

    @if ($errors->any())
    <div style="color:red; margin: 0 0 20px 0;">
        @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <form method="POST"
        action="{{ route('admin.attendances.update', $attendance->id) }}"
        novalidate>
        @csrf

        <div class="admin-detail-card">

            <!-- 名前 -->
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">
                    {{ $attendance->user->name }}
                </div>
            </div>

            <!-- 日付 -->
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

            <!-- 出勤退勤 -->
            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value time-pair">
                    <input type="time"
                        name="clock_in"
                        value="{{ old('clock_in', $clockIn ? $clockIn->format('H:i') : '') }}">

                    <span>〜</span>

                    <input type="time"
                        name="clock_out"
                        value="{{ old('clock_out', $clockOut ? $clockOut->format('H:i') : '') }}">
                </div>
            </div>

            <!-- 休憩1 -->
            @php $break1 = $attendance->breaks[0] ?? null; @endphp
            <div class="detail-row">
                <div class="detail-label">休憩</div>
                <div class="detail-value time-pair">
                    <input type="time"
                        name="break1_start"
                        value="{{ old('break1_start', ($break1 && $break1->break_start) ? \Carbon\Carbon::parse($break1->break_start)->format('H:i') : '') }}">

                    <span>〜</span>

                    <input type="time"
                        name="break1_end"
                        value="{{ old('break1_end', ($break1 && $break1->break_end) ? \Carbon\Carbon::parse($break1->break_end)->format('H:i') : '') }}">
                </div>
            </div>

            <!-- 休憩2 -->
            @php $break2 = $attendance->breaks[1] ?? null; @endphp
            <div class="detail-row">
                <div class="detail-label">休憩2</div>
                <div class="detail-value time-pair">
                    <input type="time"
                        name="break2_start"
                        value="{{ old('break2_start', ($break2 && $break2->break_start) ? \Carbon\Carbon::parse($break2->break_start)->format('H:i') : '') }}">

                    <span>〜</span>

                    <input type="time"
                        name="break2_end"
                        value="{{ old('break2_end', ($break2 && $break2->break_end) ? \Carbon\Carbon::parse($break2->break_end)->format('H:i') : '') }}">
                </div>
            </div>

            <!-- 備考 -->
            <div class="detail-row">
                <div class="detail-label">備考</div>
                <div class="detail-value">
                    <textarea name="note">{{ old('note', $correction->request_note ?? '') }}</textarea>
                </div>
            </div>

        </div>

        <div class="detail-button-area">
            <button type="submit"
                class="detail-edit-button">
                修正
            </button>
        </div>

    </form>

</div>

@endsection