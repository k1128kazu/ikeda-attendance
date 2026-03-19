<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 休憩入できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => 'working'
        ]);

        $this->actingAs($user);

        $this->post('/attendance/break-in');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id
        ]);
    }
}
