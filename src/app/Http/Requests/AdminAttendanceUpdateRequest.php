<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            | 2) 休憩開始（出勤〜退勤の範囲内）
            |--------------------------------------------------------------------------
            */
            'break1_start' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
            ],
            'break2_start' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
            ],

            /*
            |--------------------------------------------------------------------------
            | 3) 休憩終了
            | - 休憩開始より後
            | - 退勤より後は禁止
            | - 終了だけ入力された場合もエラーにする
            |--------------------------------------------------------------------------
            */
            'break1_end' => [
                'nullable',
                'date_format:H:i',
                'required_with:break1_start',
                'after:break1_start',
                'before_or_equal:clock_out',
            ],
            'break2_end' => [
                'nullable',
                'date_format:H:i',
                'required_with:break2_start',
                'after:break2_start',
                'before_or_equal:clock_out',
            ],

            /*
            |--------------------------------------------------------------------------
            | 4) 備考
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
            // 1) 出勤・退勤（仕様メッセージ）
            'clock_in.before'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after'  => '出勤時間もしくは退勤時間が不適切な値です',

            // 2) 休憩開始（仕様メッセージ）
            'break1_start.after_or_equal'  => '休憩時間が不適切な値です',
            'break1_start.before_or_equal' => '休憩時間が不適切な値です',
            'break2_start.after_or_equal'  => '休憩時間が不適切な値です',
            'break2_start.before_or_equal' => '休憩時間が不適切な値です',

            // 3) 休憩終了（仕様メッセージ）
            // - 開始より後でない → 「休憩時間が不適切」
            'break1_end.after' => '休憩時間が不適切な値です',
            'break2_end.after' => '休憩時間が不適切な値です',

            // - 退勤より後 → 「休憩時間もしくは退勤時間が不適切」
            'break1_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'break2_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',

            // - 終了だけ入力された → 「休憩時間が不適切」
            'break1_end.required_with' => '休憩時間が不適切な値です',
            'break2_end.required_with' => '休憩時間が不適切な値です',

            // 4) 備考（仕様メッセージ）
            'note.required' => '備考を記入してください',
        ];
    }
}
