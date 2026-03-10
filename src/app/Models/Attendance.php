<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'note',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    // 勤怠の所有ユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 勤怠に紐づく休憩（複数）
    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    // 勤怠に対する修正申請（複数可：履歴保持）
    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }
    protected $casts = [
        'work_date' => 'date',
        'clock_in'  => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];
}
