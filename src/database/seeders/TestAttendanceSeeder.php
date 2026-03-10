<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestAttendanceSeeder extends Seeder
{
    public function run()
    {

        $attendanceId = DB::table('attendances')->insertGetId([
            'user_id' => 1,
            'work_date' => '2025-03-06',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => 'テスト',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('attendance_breaks')->insert([
            'attendance_id' => $attendanceId,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requestId = DB::table('attendance_correction_requests')->insertGetId([
            'attendance_id' => $attendanceId,
            'user_id' => 1,
            'request_clock_in' => '09:00:00',
            'request_clock_out' => '18:00:00',
            'request_note' => '休憩修正テスト',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('attendance_correction_breaks')->insert([
            'attendance_correction_request_id' => $requestId,
            'break_start' => '12:00:00',
            'break_end' => '13:10:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('attendance_correction_breaks')->insert([
            'attendance_correction_request_id' => $requestId,
            'break_start' => '15:00:00',
            'break_end' => '15:20:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
