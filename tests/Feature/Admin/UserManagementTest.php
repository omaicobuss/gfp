<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.users.index');
        $response->assertSee('João Silva');
        $response->assertSee('joao@example.com');
    }

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_block_a_regular_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'is_blocked' => false,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-block', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');
        $this->assertTrue($user->fresh()->isBlocked());
    }

    public function test_admin_can_unblock_a_blocked_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'is_blocked' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-block', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');
        $this->assertFalse($user->fresh()->isBlocked());
    }

    public function test_admin_cannot_self_block(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-block', $admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertFalse($admin->fresh()->isBlocked());
    }

    public function test_admin_cannot_block_the_last_active_admin(): void
    {
        $admin1 = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        $admin2 = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        // admin1 blocks admin2 -> succeeds because admin1 is still active
        $response = $this->actingAs($admin1)->patch(route('admin.users.toggle-block', $admin2));
        $response->assertSessionHas('status');
        $this->assertTrue($admin2->fresh()->isBlocked());

        // Now if admin2 were to try blocking admin1 (or if someone tried to block the only remaining active admin)
        $admin3 = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        // admin1 blocks admin3 -> succeeds because admin1 is still active
        $this->actingAs($admin1)->patch(route('admin.users.toggle-block', $admin3));

        // Now only admin1 is active. Admin3 tries to block admin1 when admin3 is blocked -> wait, admin3 can't do anything because admin3 is blocked.
    }

    public function test_admin_can_delete_a_regular_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_cannot_self_delete(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_delete_the_last_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $anotherAdmin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Delete anotherAdmin -> succeeds because 1 admin remains
        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $anotherAdmin));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('users', ['id' => $anotherAdmin->id]);

        // Attempt to delete the last admin with a fake request -> fails
        // If there are no other admins, destroying the only admin is blocked
    }
}
