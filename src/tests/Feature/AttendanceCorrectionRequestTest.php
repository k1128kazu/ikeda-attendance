<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 修正申請作成()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/' . $attendance->id . '/request', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => [],
            'break_end' => [],
            'note' => 'テスト'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test */
    public function 複数休憩が登録できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $this->post('/attendance/' . $attendance->id . '/request', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['10:00', '13:00', '15:00'],
            'break_end' => ['10:30', '13:30', '15:30'],
            'note' => 'テスト'
        ]);

        $this->assertDatabaseCount('attendance_correction_breaks', 3);
    }

    /** @test */
    public function 空の休憩は保存されない()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $this->post('/attendance/' . $attendance->id . '/request', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['10:00', null],
            'break_end' => ['10:30', null],
            'note' => 'テスト'
        ]);

        $this->assertDatabaseCount('attendance_correction_breaks', 1);
    }

    /** @test */
    public function 申請中は修正できない()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        \App\Models\AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_clock_in' => '09:00',
            'request_clock_out' => '18:00',
            'request_note' => 'テスト',
            'status' => 'pending'
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/' . $attendance->id . '/request', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => 'テスト'
        ]);

        $response->assertSessionHasErrors('attendance');
    }
}
