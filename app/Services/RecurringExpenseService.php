<?php

namespace App\Services;

use App\Models\RecurringExpense;
use App\Models\RecurringExpenseOccurrence;
use Carbon\Carbon;

class RecurringExpenseService
{
    /**
     * Generate future occurrences for a recurring expense up to a given date (FR-015, FR-020).
     */
    public static function generateOccurrences(RecurringExpense $recurring, ?Carbon $untilDate = null): int
    {
        $until = $untilDate ? $untilDate->copy()->endOfDay() : Carbon::now()->addMonths(3)->endOfDay();
        $currentDate = $recurring->due_date->copy()->startOfDay();
        $createdCount = 0;
        $maxIterations = 52; // Trava de segurança
        $iteration = 0;

        while ($currentDate->lte($until) && $iteration < $maxIterations) {
            $iteration++;
            $dateString = $currentDate->toDateString();

            // Verifica se a ocorrência já existe nesta data de vencimento (usando whereDate)
            $exists = RecurringExpenseOccurrence::where('recurring_expense_id', $recurring->id)
                ->whereDate('due_date', $dateString)
                ->exists();

            if (! $exists) {
                $status = ExpenseStatusService::calculateStatus($dateString, false, null);

                RecurringExpenseOccurrence::create([
                    'user_id' => $recurring->user_id,
                    'recurring_expense_id' => $recurring->id,
                    'category_id' => $recurring->category_id,
                    'description' => $recurring->description,
                    'due_date' => $dateString,
                    'expected_amount' => $recurring->expected_amount,
                    'actual_amount' => null,
                    'status' => $status,
                    'paid_at' => null,
                    'notes' => $recurring->notes,
                ]);

                $createdCount++;
            }

            // Avança para o próximo vencimento
            $nextDate = $recurring->calculateNextDueDate($currentDate);

            // Se por algum motivo a data não avançou, interrompe o loop
            if ($nextDate->lte($currentDate)) {
                break;
            }

            $currentDate = $nextDate;
        }

        return $createdCount;
    }

    /**
     * Generate occurrences for all active recurring expenses of a user.
     */
    public static function generateAllForUser(int $userId, ?Carbon $untilDate = null): int
    {
        $activeRecurring = RecurringExpense::forUser($userId)->active()->get();
        $totalCreated = 0;

        foreach ($activeRecurring as $recurring) {
            $totalCreated += self::generateOccurrences($recurring, $untilDate);
        }

        return $totalCreated;
    }
}
