<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\AttendanceCorrectionRequest;

class CorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 申請一覧に自分の申請が表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        AttendanceCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_clock_in' => '09:00',
            'request_clock_out' => '18:00',
            'request_note' => '修正',
            'status' => 'pending'
        ]);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('修正');
    }
}
