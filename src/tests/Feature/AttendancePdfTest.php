<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\AttendanceCorrectionRequest;

class AttendancePdfTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録_名前未入力()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function 会員登録_メール未入力()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 会員登録_パスワード8文字未満()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass'
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 会員登録_パスワード不一致()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 会員登録_正常登録()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function ログイン_メール未入力()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function ログイン_パスワード未入力()
    {
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => ''
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function ログイン失敗()
    {
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function 出勤できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function 出勤は一日一回のみ()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString()
        ]);
        $this->actingAs($user);

        $response = $this->post('/attendance/clock-in');

        $response->assertStatus(302);
    }

    /** @test */
    public function 休憩開始できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => 'working'
        ]);
        $this->actingAs($user);

        $this->post('/attendance/break-in');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id
        ]);
    }
    /** @test */
    public function 休憩終了できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => 'breaking'
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/break-out');

        $response->assertStatus(302);
    }
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

        $this->post('/attendance/clock-out');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'finished'
        ]);
    }
    /** @test */
    public function 勤怠一覧表示()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
    }

    /** @test */
    public function 修正申請作成()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished'
        ]);

        $request = AttendanceCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_clock_in' => '09:00',
            'request_clock_out' => '18:00',
            'request_note' => '修正',
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id
        ]);
    }
        /** @test */
    public function 修正申請承認()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString()
        ]);

        $request = AttendanceCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_clock_in' => '09:00',
            'request_clock_out' => '18:00',
            'request_note' => '修正',
            'status' => 'pending'
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/corrections/' . $request->id . '/approve');

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $request->id,
            'status' => 'approved'
        ]);
    }
    /** @test */
    public function 管理者ログインできる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password'
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function 管理者_修正申請一覧表示()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/corrections');

        $response->assertStatus(200);
    }

    /** @test */
    public function 管理者_修正申請詳細表示()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString()
        ]);

        $request = AttendanceCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_clock_in' => '09:00',
            'request_clock_out' => '18:00',
            'request_note' => '修正',
            'status' => 'pending'
        ]);

        $this->actingAs($admin);

        $response = $this->get('/stamp_correction_request/' . $request->id);

        $response->assertStatus(200);
    }



}
