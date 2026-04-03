<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AttendanceCorrectionRequest;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => [
                'required',
                'date_format:H:i',
            ],
            'clock_out' => [
                'nullable',
                'date_format:H:i',
                'after:clock_in',
            ],

            'break_start.*' => [
                'nullable',
                'date_format:H:i',
            ],
            'break_end.*' => [
                'nullable',
                'date_format:H:i',
            ],

            'note' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時刻は必ず入力してください',
            'clock_out.after'   => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start.*.date_format' => '休憩時間が不適切な値です',
            'break_end.*.date_format'   => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $attendanceId = $this->route('id');

            /*
            ----------------------------------
            承認待ちチェック
            ----------------------------------
            */
            $hasPending = AttendanceCorrectionRequest::where('attendance_id', $attendanceId)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                $validator->errors()->add(
                    'attendance',
                    '承認待ちの申請があるため編集できません'
                );
                return;
            }

            /*
            ----------------------------------
            休憩ロジックチェック（修正版）
            ----------------------------------
            */
            $clockIn  = $this->clock_in;
            $clockOut = $this->clock_out;

            $starts = $this->break_start ?? [];
            $ends   = $this->break_end ?? [];

            foreach ($starts as $i => $start) {

                $end = $ends[$i] ?? null;

                // 両方空 → 無視
                if (!$start && !$end) continue;

                // 片方だけ → エラー
                if (!$start || !$end) {
                    $validator->errors()->add(
                        "break_start.$i",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                // start >= end
                if (Carbon::createFromFormat('H:i', $start)
                    ->gte(Carbon::createFromFormat('H:i', $end))
                ) {

                    $validator->errors()->add(
                        "break_start.$i",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                // 出勤前
                if ($clockIn && Carbon::createFromFormat('H:i', $start)
                    ->lt(Carbon::createFromFormat('H:i', $clockIn))
                ) {

                    $validator->errors()->add(
                        "break_start.$i",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                // 退勤後
                if ($clockOut && Carbon::createFromFormat('H:i', $end)
                    ->gt(Carbon::createFromFormat('H:i', $clockOut))
                ) {

                    $validator->errors()->add(
                        "break_end.$i",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
