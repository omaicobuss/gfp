<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('SenhaAntiga@123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'SenhaAntiga@123',
                'password' => 'NovaSenhaForte@789',
                'password_confirmation' => 'NovaSenhaForte@789',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('NovaSenhaForte@789', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('SenhaAntiga@123'),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'SenhaErrada@000',
                'password' => 'NovaSenhaForte@789',
                'password_confirmation' => 'NovaSenhaForte@789',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }
}
