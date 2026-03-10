<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionRequestSeeder extends Seeder
{
    public function run()
    {

        $attendances = DB::table('attendances')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        foreach ($attendances as $attendance) {

            $requestId = DB::table('attendance_correction_requests')->insertGetId([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'request_clock_in' => $attendance->clock_in,
                'request_clock_out' => $attendance->clock_out,
                'request_note' => '休憩修正申請テスト',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('attendance_correction_breaks')->insert([
                [
                    'attendance_correction_request_id' => $requestId,
                    'break_start' => '12:00:00',
                    'break_end' => '13:10:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'attendance_correction_request_id' => $requestId,
                    'break_start' => '15:00:00',
                    'break_end' => '15:20:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }
}
