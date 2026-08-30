<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\RecurringExpense;
use App\Models\RecurringExpenseOccurrence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsolidatedExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_consolidated_view_displays_both_single_and_recurring_expenses(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        // Gasto Avulso
        $single = Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Combustível Posto ABC',
            'amount' => 120.00,
            'date' => Carbon::today()->toDateString(),
        ]);

        // Gasto Recorrente e Ocorrência
        $recurring = RecurringExpense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Internet Fibra Óptica',
            'expected_amount' => 99.90,
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $occurrence = RecurringExpenseOccurrence::factory()->create([
            'user_id' => $user->id,
            'recurring_expense_id' => $recurring->id,
            'category_id' => $category->id,
            'description' => 'Internet Fibra Óptica',
            'expected_amount' => 99.90,
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee('Combustível Posto ABC');
        $response->assertSee('Internet Fibra Óptica');
        $response->assertSee('🔹 Avulso');
        $response->assertSee('🔄 Recorrente');
    }

    public function test_user_can_filter_by_type_single_or_recurring(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Padaria da Esquina',
        ]);

        RecurringExpenseOccurrence::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Condomínio Mensal',
        ]);

        // Filtrar apenas avulsos
        $responseSingle = $this->actingAs($user)->get(route('expenses.index', ['type' => 'single']));
        $responseSingle->assertSee('Padaria da Esquina');
        $responseSingle->assertDontSee('Condomínio Mensal');

        // Filtrar apenas recorrentes
        $responseRecurring = $this->actingAs($user)->get(route('expenses.index', ['type' => 'recurring']));
        $responseRecurring->assertSee('Condomínio Mensal');
        $responseRecurring->assertDontSee('Padaria da Esquina');
    }
}
