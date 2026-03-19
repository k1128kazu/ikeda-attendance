<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => 'working'
        ]);

        $this->actingAs($user);

        $this->post('/attendance/clock-out'); // ←ここも重要

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'finished'
        ]);
    }
}
