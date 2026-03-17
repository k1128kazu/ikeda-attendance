<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が全ユーザーの勤怠を見れる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }
}
