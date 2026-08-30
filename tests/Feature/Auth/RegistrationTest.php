<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_strong_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'role' => 'user',
            'is_blocked' => false,
            'email_verified_at' => null,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
    }

    public function test_registration_fails_with_weak_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Usuário Fraco',
            'email' => 'fraco@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'fraco@example.com',
        ]);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicado@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Outro Usuário',
            'email' => 'duplicado@example.com',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
