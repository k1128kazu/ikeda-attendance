<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細に正しい情報が表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('2025年');
        $response->assertSee('1月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
