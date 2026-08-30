<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_categories_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertOk();
        $response->assertViewIs('categories.index');
        $response->assertSee('Minhas Categorias');
    }

    public function test_new_user_gets_default_outros_category_automatically(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Outros',
        ]);
    }

    public function test_user_can_create_a_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Alimentação',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Alimentação',
        ]);
    }

    public function test_user_cannot_create_category_with_duplicate_name(): void
    {
        $user = User::factory()->create();

        Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Aluguel',
        ]);

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Aluguel',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_different_users_can_have_categories_with_same_name(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Category::create([
            'user_id' => $userA->id,
            'name' => 'Transporte',
        ]);

        $response = $this->actingAs($userB)->post(route('categories.store'), [
            'name' => 'Transporte',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', [
            'user_id' => $userB->id,
            'name' => 'Transporte',
        ]);
    }

    public function test_user_can_update_their_category(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Antigo Nome',
        ]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Novo Nome',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Novo Nome',
        ]);
    }

    public function test_user_cannot_update_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryA = Category::create([
            'user_id' => $userA->id,
            'name' => 'Categoria de A',
        ]);

        $response = $this->actingAs($userB)->put(route('categories.update', $categoryA), [
            'name' => 'Tentativa de Hack',
        ]);

        $response->assertForbidden();
        $this->assertEquals('Categoria de A', $categoryA->fresh()->name);
    }

    public function test_user_can_delete_their_unused_category(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Para Deletar',
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryA = Category::create([
            'user_id' => $userA->id,
            'name' => 'Categoria de A',
        ]);

        $response = $this->actingAs($userB)->delete(route('categories.destroy', $categoryA));

        $response->assertForbidden();
        $this->assertDatabaseHas('categories', [
            'id' => $categoryA->id,
        ]);
    }

    public function test_guest_cannot_access_categories(): void
    {
        $response = $this->get(route('categories.index'));
        $response->assertRedirect(route('login'));
    }
}
