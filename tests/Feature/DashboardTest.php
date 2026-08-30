<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard');
        $response->assertSee('Painel Financeiro');
    }

    public function test_dashboard_displays_correct_monthly_metrics(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        // Gasto pago no mês atual
        Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Gasto Pago Teste',
            'amount' => 200.00,
            'date' => Carbon::now()->startOfMonth()->addDays(2)->toDateString(),
            'status' => 'paid',
        ]);

        // Gasto pendente no mês atual
        Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Gasto Pendente Teste',
            'amount' => 150.00,
            'date' => Carbon::now()->startOfMonth()->addDays(5)->toDateString(),
            'status' => 'pending',
        ]);

        // Gasto de outro usuário (não deve impactar métricas)
        $userB = User::factory()->create();
        $categoryB = Category::where('user_id', $userB->id)->first();
        Expense::factory()->create([
            'user_id' => $userB->id,
            'category_id' => $categoryB->id,
            'amount' => 9999.00,
            'date' => Carbon::now()->toDateString(),
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Gasto Pago Teste');
        $response->assertSee('Gasto Pendente Teste');
        $response->assertDontSee('9.999,00');
    }
}
