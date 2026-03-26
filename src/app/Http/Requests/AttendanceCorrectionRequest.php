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
            'break_end.*' => ['nullable', 'date_format:H:i'],

            'note' => ['required', 'string']
        ];
    }

    public function messages()
    {
        return [

            'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start.*.date_format' => '休憩時間が不適切な値です',
            'break_end.*.date_format' => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $attendanceId = $this->route('attendance') ?? $this->route('id');

            if (!$attendanceId) {
                return;
            }

            /*
            ----------------------------------
            承認待ち申請チェック
            ----------------------------------
            */

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

            /*
            ----------------------------------
            退勤前チェック（追加）
            ----------------------------------
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
            ----------------------------------
            出勤退勤チェック
            ----------------------------------
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
            ----------------------------------
            休憩チェック
            ----------------------------------
            */

            $breakStarts = $this->break_start ?? [];
            $breakEnds = $this->break_end ?? [];

            foreach ($breakStarts as $index => $start) {

                $end = $breakEnds[$index] ?? null;

                if (!$start && !$end) {
                    continue;
                }

                if ($start && $end && $start >= $end) {

                    $validator->errors()->add(
                        'break_start',
                        '休憩時間が不適切な値です'
                    );
                }

                if ($clockIn && $start && $start < $clockIn) {

                    $validator->errors()->add(
                        'break_start',
                        '休憩時間が不適切な値です'
                    );
                }

                if ($clockOut && $end && $end > $clockOut) {

                    $validator->errors()->add(
                        'break_start',
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
