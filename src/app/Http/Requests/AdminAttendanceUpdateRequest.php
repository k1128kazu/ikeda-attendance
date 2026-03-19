<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AttendanceCorrectionRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    // 1つだけ表示（複数同時に出さない）
    protected $stopOnFirstFailure = true;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | 1) 出勤・退勤（相互比較）
            |--------------------------------------------------------------------------
            */
            'clock_in' => [
                'required',
                'date_format:H:i',
                'before:clock_out',
            ],
            'clock_out' => [
                'required',
                'date_format:H:i',
                'after:clock_in',
            ],

            /*
            |--------------------------------------------------------------------------
            | 2) 休憩（可変）
            |--------------------------------------------------------------------------
            */
            'break_start.*' => [
                'nullable',
                'date_format:H:i',
            ],
            'break_end.*' => [
                'nullable',
                'date_format:H:i',
            ],

            /*
            |--------------------------------------------------------------------------
            | 3) 備考
            |--------------------------------------------------------------------------
            */
            'note' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            // 出勤・退勤
            'clock_in.before'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after'  => '出勤時間もしくは退勤時間が不適切な値です',

            // 休憩
            'break_start.*.date_format' => '休憩時間が不適切な値です',
            'break_end.*.date_format'   => '休憩時間が不適切な値です',

            // 備考
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
            休憩ロジックチェック（可変）
            ----------------------------------
            */
            $clockIn  = $this->clock_in;
            $clockOut = $this->clock_out;

            $starts = $this->break_start ?? [];
            $ends   = $this->break_end ?? [];

            foreach ($starts as $i => $start) {

                $end = $ends[$i] ?? null;

                // 両方空はOK
                if (!$start && !$end) continue;

                // 片方だけ入力 → NG
                if (!$start || !$end) {
                    $validator->errors()->add(
                        "break_start.$i",
                        '休憩時間が不適切な値です'
                    );
                    return;
                }

                // 開始 >= 終了 → NG
                if ($start >= $end) {
                    $validator->errors()->add(
                        "break_start.$i",
                        '休憩時間が不適切な値です'
                    );
                    return;
                }

                // 出勤前 → NG
                if ($clockIn && $start < $clockIn) {
                    $validator->errors()->add(
                        "break_start.$i",
                        '休憩時間が不適切な値です'
                    );
                    return;
                }

                // 退勤後 → NG
                if ($clockOut && $end > $clockOut) {
                    $validator->errors()->add(
                        "break_end.$i",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                    return;
                }
            }
        });
    }
}
