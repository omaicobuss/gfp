<?php

namespace Tests\Feature;

use App\Mail\DueExpenseReminderMail;
use App\Models\Category;
use App\Models\Expense;
use App\Models\RecurringExpense;
use App\Models\RecurringExpenseOccurrence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExpenseReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_reminder_email_for_expenses_due_today_and_in_two_days(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        $category = Category::where('user_id', $user->id)->first();

        // Gasto vencendo hoje
        Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Conta de Luz',
            'amount' => 180.00,
            'date' => Carbon::today()->toDateString(),
            'status' => 'pending',
        ]);

        // Ocorrência vencendo em 2 dias
        $recurring = RecurringExpense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'due_date' => Carbon::today()->addDays(2)->toDateString(),
        ]);

        RecurringExpenseOccurrence::factory()->create([
            'user_id' => $user->id,
            'recurring_expense_id' => $recurring->id,
            'category_id' => $category->id,
            'description' => 'Internet Fibra',
            'expected_amount' => 120.00,
            'due_date' => Carbon::today()->addDays(2)->toDateString(),
            'status' => 'pending',
        ]);

        // Executar comando
        $this->artisan('expenses:send-reminders --days=2')
            ->assertSuccessful();

        Mail::assertSent(DueExpenseReminderMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->expenses->contains('description', 'Conta de Luz')
                && $mail->occurrences->contains('description', 'Internet Fibra');
        });
    }

    public function test_command_does_not_send_reminder_for_paid_expenses(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        $category = Category::where('user_id', $user->id)->first();

        // Gasto já pago vencendo hoje
        Expense::factory()->paid()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => Carbon::today()->toDateString(),
        ]);

        $this->artisan('expenses:send-reminders --days=2')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_command_does_not_send_reminder_to_unverified_or_blocked_users(): void
    {
        Mail::fake();

        // Usuário não verificado
        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
            'is_blocked' => false,
        ]);
        $cat1 = Category::where('user_id', $unverifiedUser->id)->first();
        Expense::factory()->create([
            'user_id' => $unverifiedUser->id,
            'category_id' => $cat1->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'pending',
        ]);

        // Usuário bloqueado
        $blockedUser = User::factory()->create([
            'email_verified_at' => now(),
            'is_blocked' => true,
        ]);
        $cat2 = Category::where('user_id', $blockedUser->id)->first();
        Expense::factory()->create([
            'user_id' => $blockedUser->id,
            'category_id' => $cat2->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'pending',
        ]);

        $this->artisan('expenses:send-reminders --days=2')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_reminder_email_renders_content_properly(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Fatura Cartão',
            'amount' => 450.00,
            'date' => Carbon::today()->toDateString(),
        ]);

        $mailable = new DueExpenseReminderMail($user, collect([$expense]), collect());

        $mailable->assertSeeInHtml('Olá, ' . $user->name);
        $mailable->assertSeeInHtml('Fatura Cartão');
        $mailable->assertSeeInHtml('R$ 450,00');
    }
}
