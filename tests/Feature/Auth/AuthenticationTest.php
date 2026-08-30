<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_verified_and_active_users_can_authenticate(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_blocked' => false,
            'password' => bcrypt('SenhaValida@123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SenhaValida@123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_unverified_users_cannot_authenticate(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => bcrypt('SenhaValida@123'),
            'is_blocked' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SenhaValida@123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_blocked_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_blocked' => true,
            'password' => bcrypt('SenhaValida@123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SenhaValida@123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_blocked' => false,
            'password' => bcrypt('SenhaValida@123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SenhaIncorreta@999',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_rate_limiting_locks_out_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_blocked' => false,
            'password' => bcrypt('SenhaValida@123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'SenhaErrada@000',
            ]);
        }

        // 6th attempt should trigger rate limiting throttle
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SenhaValida@123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
