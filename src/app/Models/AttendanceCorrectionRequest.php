<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'request_clock_in',
        'request_clock_out',
        'request_note',
        'status',
        'approved_by',
        'approved_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    // 対象となる勤怠
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 申請者
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 承認者（管理者）
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // 修正休憩明細
    public function breaks()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class);
    }
}
