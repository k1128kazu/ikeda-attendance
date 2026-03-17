<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者がスタッフ一覧を見れる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee($user->email);
    }
}
