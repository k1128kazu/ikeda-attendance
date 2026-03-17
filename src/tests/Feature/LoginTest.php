<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メール未入力エラー()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワード未入力エラー()
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
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@test.com',
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors();
    }
}
