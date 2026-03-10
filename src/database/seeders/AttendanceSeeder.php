<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', '!=', 'admin')->get();

        foreach ($users as $user) {

            for ($i = 1; $i <= 10; $i++) {

                $date = Carbon::now()->subDays($i);

                $clockIn  = Carbon::createFromTime(8, rand(45, 59));
                $clockOut = Carbon::createFromTime(17, rand(45, 59));

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in' => $clockIn->format('H:i:s'),
                    'clock_out' => $clockOut->format('H:i:s'),
                    'note' => '通常勤務',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 昼休憩
                DB::table('attendance_breaks')->insert([
                    'attendance_id' => $attendanceId,
                    'break_start' => '12:00:00',
                    'break_end' => '13:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // たまに午後休憩
                if (rand(0, 1)) {
                    DB::table('attendance_breaks')->insert([
                        'attendance_id' => $attendanceId,
                        'break_start' => '15:00:00',
                        'break_end' => '15:15:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
