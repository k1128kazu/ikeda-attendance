<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthRegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 名前未入力でバリデーションエラー()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function メール未入力でバリデーションエラー()
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
    public function パスワード8文字未満エラー()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'test@test.com',
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function パスワード不一致エラー()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 正常登録()
    {
        $response = $this->post('/register', [
            'name' => 'テスト',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com'
        ]);
    }
}
