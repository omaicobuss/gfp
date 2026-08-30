<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_expenses_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertViewIs('expenses.index');
        $response->assertSee('Gastos e Despesas');
    }

    public function test_user_can_create_a_non_recurring_expense(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'description' => 'Supermercado Mensal',
            'category_id' => $category->id,
            'amount' => '350.50',
            'date' => Carbon::tomorrow()->toDateString(),
            'is_paid' => false,
            'notes' => 'Compras do mês no atacado',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Supermercado Mensal',
            'amount' => 350.50,
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    public function test_expense_created_with_past_date_and_unpaid_becomes_overdue(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'description' => 'Boleto Vencido',
            'category_id' => $category->id,
            'amount' => '100.00',
            'date' => Carbon::yesterday()->toDateString(),
            'is_paid' => false,
        ]);

        $response->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'description' => 'Boleto Vencido',
            'status' => 'overdue',
        ]);
    }

    public function test_expense_created_as_paid_gets_paid_status(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'description' => 'Almoço Pago',
            'category_id' => $category->id,
            'amount' => '45.00',
            'date' => Carbon::today()->toDateString(),
            'is_paid' => true,
        ]);

        $response->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'description' => 'Almoço Pago',
            'status' => 'paid',
        ]);
    }

    public function test_user_cannot_create_expense_with_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryB = Category::where('user_id', $userB->id)->first();

        $response = $this->actingAs($userA)->post(route('expenses.store'), [
            'description' => 'Tentativa Inválida',
            'category_id' => $categoryB->id,
            'amount' => '50.00',
            'date' => Carbon::today()->toDateString(),
        ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('expenses', [
            'description' => 'Tentativa Inválida',
        ]);
    }

    public function test_user_can_update_their_expense(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Descrição Antiga',
            'amount' => 100.00,
            'date' => Carbon::today()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->put(route('expenses.update', $expense), [
            'description' => 'Descrição Atualizada',
            'category_id' => $category->id,
            'amount' => '120.00',
            'date' => Carbon::today()->toDateString(),
            'is_paid' => true,
        ]);

        $response->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Descrição Atualizada',
            'amount' => 120.00,
            'status' => 'paid',
        ]);
    }

    public function test_user_cannot_update_another_users_expense(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryA = Category::where('user_id', $userA->id)->first();

        $expenseA = Expense::factory()->create([
            'user_id' => $userA->id,
            'category_id' => $categoryA->id,
            'description' => 'Gasto de A',
        ]);

        $response = $this->actingAs($userB)->put(route('expenses.update', $expenseA), [
            'description' => 'Tentativa de Hack',
            'category_id' => $categoryA->id,
            'amount' => '999.00',
            'date' => Carbon::today()->toDateString(),
        ]);

        $response->assertForbidden();
        $this->assertEquals('Gasto de A', $expenseA->fresh()->description);
    }

    public function test_user_can_delete_their_expense(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('expenses.destroy', $expense));

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_expense(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryA = Category::where('user_id', $userA->id)->first();

        $expenseA = Expense::factory()->create([
            'user_id' => $userA->id,
            'category_id' => $categoryA->id,
        ]);

        $response = $this->actingAs($userB)->delete(route('expenses.destroy', $expenseA));

        $response->assertForbidden();
        $this->assertDatabaseHas('expenses', [
            'id' => $expenseA->id,
        ]);
    }

    public function test_user_cannot_delete_category_in_use_by_an_expense(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_can_filter_expenses_by_status_period_and_category(): void
    {
        $user = User::factory()->create();
        $category1 = Category::where('user_id', $user->id)->first();
        $category2 = Category::create(['user_id' => $user->id, 'name' => 'Lazer']);

        // Gasto 1: Pago, Categoria 1, Hoje
        $exp1 = Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category1->id,
            'description' => 'Mercado Central',
            'amount' => 150.00,
            'date' => '2026-08-15',
            'status' => 'paid',
        ]);

        // Gasto 2: Pendente, Categoria 2, Outra Data
        $exp2 = Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category2->id,
            'description' => 'Cinema',
            'amount' => 60.00,
            'date' => '2026-08-25',
            'status' => 'pending',
        ]);

        // Filtro por status=paid
        $response = $this->actingAs($user)->get(route('expenses.index', ['status' => 'paid']));
        $response->assertSee('Mercado Central');
        $response->assertDontSee('Cinema');

        // Filtro por categoria2
        $response = $this->actingAs($user)->get(route('expenses.index', ['category_id' => $category2->id]));
        $response->assertSee('Cinema');
        $response->assertDontSee('Mercado Central');

        // Filtro por período
        $response = $this->actingAs($user)->get(route('expenses.index', [
            'start_date' => '2026-08-20',
            'end_date' => '2026-08-30',
        ]));
        $response->assertSee('Cinema');
        $response->assertDontSee('Mercado Central');
    }
}
