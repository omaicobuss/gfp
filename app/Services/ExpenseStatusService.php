<?php

namespace App\Services;

use Carbon\Carbon;

class ExpenseStatusService
{
    /**
     * Calculate status for an expense or occurrence (FR-020).
     *
     * @param string|Carbon $date
     * @param bool $isPaid
     * @param string|Carbon|null $paidAt
     * @return string ('paid' | 'pending' | 'overdue')
     */
    public static function calculateStatus(string|Carbon $date, bool $isPaid = false, string|Carbon|null $paidAt = null): string
    {
        if ($isPaid || ! empty($paidAt)) {
            return 'paid';
        }

        $expenseDate = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();
        $today = Carbon::today();

        if ($expenseDate->lt($today)) {
            return 'overdue';
        }

        return 'pending';
    }

    /**
     * Get human-readable label for status.
     */
    public static function getStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'overdue' => 'Atrasado',
            default => ucfirst($status),
        };
    }

    /**
     * Get badge CSS classes for status.
     */
    public static function getStatusBadgeClasses(string $status): string
    {
        return match ($status) {
            'paid' => 'bg-green-100 text-green-800 border-green-200',
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            'overdue' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
