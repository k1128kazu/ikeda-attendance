<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠一覧が表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
    }

    /** @test */
    public function 勤怠一覧に自分のデータが表示される()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
