<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤より後だとエラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/' . $attendance->id . '/request', [
            'clock_in' => '20:00',
            'clock_out' => '10:00',
            'note' => '修正'
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function 備考未入力でエラー()
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
            'note' => ''
        ]);

        $response->assertSessionHasErrors('note');
    }
}
