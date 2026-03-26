<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;

class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 修正申請承認()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => '08:00:00',
            'clock_out' => '17:00:00',
            'status'    => 'finished',
            'note'      => 'テスト',
        ]);

        $request = AttendanceCorrectionRequest::create([
            'user_id'           => $user->id,
            'attendance_id'     => $attendance->id,
            'request_clock_in'  => '09:00',
            'request_clock_out' => '18:00',
            'request_note'      => '修正',
            'status'            => 'pending'
        ]);

        $this->actingAs($admin);

        $this->post('/admin/corrections/' . $request->id . '/approve');

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id'     => $request->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function 承認で勤怠が更新される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => '08:00:00',
            'clock_out' => '17:00:00',
            'status'    => 'finished',
            'note'      => 'テスト',
        ]);

        $request = AttendanceCorrectionRequest::create([
            'user_id'           => $user->id,
            'attendance_id'     => $attendance->id,
            'request_clock_in'  => '09:00',
            'request_clock_out' => '18:00',
            'request_note'      => '修正',
            'status'            => 'pending'
        ]);

        $this->actingAs($admin);

        $this->post('/admin/corrections/' . $request->id . '/approve');

        $this->assertDatabaseHas('attendances', [
            'id'        => $attendance->id,
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }
}
