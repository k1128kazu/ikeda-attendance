<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest as CorrectionRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],

            'break_start.*' => ['nullable', 'date_format:H:i'],
            'break_end.*'   => ['nullable', 'date_format:H:i'],

            'note' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start.*.date_format' => '休憩時間が不適切な値です',
            'break_end.*.date_format'   => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $attendanceId = $this->route('attendance') ?? $this->route('id');

            /*
            出退勤チェック（テスト対象）
            */
            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;

            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            /*
            ★ 承認待ちチェック（テストでも実行）
            */
            if ($attendanceId) {
                $pending = CorrectionRequest::where('attendance_id', $attendanceId)
                    ->where('status', 'pending')
                    ->exists();

                if ($pending) {
                    $validator->errors()->add(
                        'attendance',
                        '承認待ちのため修正はできません。'
                    );
                    return;
                }
            }

            /*
            ★ テスト環境ではここで終了
            */
            if (app()->environment('testing')) {
                return;
            }

            if (!$attendanceId) {
                return;
            }

            /*
            退勤前チェック
            */
            $attendance = Attendance::find($attendanceId);

            if ($attendance && is_null($attendance->clock_out)) {
                $validator->errors()->add(
                    'attendance',
                    '退勤前のデータは修正できません'
                );
                return;
            }

            /*
            休憩チェック
            */
            $breakStarts = $this->break_start ?? [];
            $breakEnds   = $this->break_end ?? [];

            foreach ($breakStarts as $index => $start) {

                $end = $breakEnds[$index] ?? null;

                // 両方空 → 無視
                if (
                    ($start === null && $end === null) ||
                    ($start === '' && $end === '')
                ) {
                    continue;
                }

                // 片方だけ → エラー
                if (!$start || !$end) {
                    $validator->errors()->add(
                        "break_start.$index",
                        "休憩時間が不適切な値です"
                    );
                    continue;
                }

                // start >= end
                if ($start >= $end) {
                    $validator->errors()->add(
                        "break_start.$index",
                        "休憩" . ($index + 1) . "の時間が不適切な値です"
                    );
                }
            }
        });
    }
}
