<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_request_id',
        'break_start',
        'break_end',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    // 親となる修正申請
    public function correctionRequest()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class);
    }
}
