<?php

use App\Models\User;

class AuthTest extends TestCase
{
    public function test_login_success()
    {
        $user = User::factory()->create();

        $this->json('POST', '/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ])
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                        'updated_at'
                    ],
                    'access_token',
                    'token_type',
                    'expires_in'
                ],
                'error'
            ]);
    }

    public function test_login_failed()
    {
        $user = User::factory()->create();

        $this->json('POST', '/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret'
        ])
            ->seeStatusCode(401)
            ->seeJson([
                'message' => 'Login Gagal'
            ])
            ->seeJsonStructure([
                'success',
                'message',
                'data',
                'error'
            ]);
    }
}
