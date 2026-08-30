<?php

namespace App\Console\Commands;

use App\Mail\DueExpenseReminderMail;
use App\Models\Expense;
use App\Models\RecurringExpenseOccurrence;
use App\Models\User;
use App\Services\RecurringExpenseService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpenseRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:send-reminders {--days=2 : Quantidade de dias de antecedência para os lembretes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia lembretes por e-mail para gastos com vencimento próximo (hoje e X dias antes)';

    /**
     * Execute the console command (FR-031, FR-032).
     */
    public function handle(): int
    {
        $daysAhead = (int) $this->option('days');
        $today = Carbon::today()->toDateString();
        $targetFutureDate = Carbon::today()->addDays($daysAhead)->toDateString();

        // Datas alvo de notificação: hoje e daqui a X dias
        $targetDates = array_unique([$today, $targetFutureDate]);

        $this->info("Buscando contas pendentes com vencimento em: " . implode(' e ', $targetDates));

        // Usuários ativos, verificados e não bloqueados
        $users = User::whereNotNull('email_verified_at')
            ->where('is_blocked', false)
            ->get();

        $totalEmailsSent = 0;

        foreach ($users as $user) {
            // Garante ocorrências geradas para o usuário
            RecurringExpenseService::generateAllForUser($user->id);

            // Gastos avulsos pendentes que vencem nas datas alvo
            $pendingExpenses = Expense::with('category')
                ->forUser($user->id)
                ->where(function ($q) use ($targetDates) {
                    foreach ($targetDates as $d) {
                        $q->orWhereDate('expenses.date', $d);
                    }
                })
                ->where('expenses.status', '!=', 'paid')
                ->get();

            // Ocorrências recorrentes pendentes que vencem nas datas alvo
            $pendingOccurrences = RecurringExpenseOccurrence::with(['category', 'recurringExpense'])
                ->forUser($user->id)
                ->where(function ($q) use ($targetDates) {
                    foreach ($targetDates as $d) {
                        $q->orWhereDate('recurring_expense_occurrences.due_date', $d);
                    }
                })
                ->where('recurring_expense_occurrences.status', '!=', 'paid')
                ->get();

            if ($pendingExpenses->isNotEmpty() || $pendingOccurrences->isNotEmpty()) {
                Mail::to($user->email)->send(
                    new DueExpenseReminderMail($user, $pendingExpenses, $pendingOccurrences)
                );

                $totalEmailsSent++;
                $this->line("  ✓ Lembrete enviado para {$user->email} ({$pendingExpenses->count()} avulsos, {$pendingOccurrences->count()} recorrentes)");
            }
        }

        $this->info("Processamento concluído! Total de {$totalEmailsSent} e-mails de lembrete enviados.");

        return Command::SUCCESS;
    }
}
