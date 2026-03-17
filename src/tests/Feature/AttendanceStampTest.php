<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function 出勤は一日一回のみ()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => 'working'
        ]);
        
        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $this->assertDatabaseCount('attendances', 1);
    }
}
