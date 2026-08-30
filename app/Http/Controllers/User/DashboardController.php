<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\RecurringExpenseOccurrence;
use App\Services\RecurringExpenseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard with consolidated monthly overview and recent activity.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // Garante que as ocorrências ativas estejam geradas
        RecurringExpenseService::generateAllForUser($userId);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // 1. Gastos não recorrentes do mês
        $singleExpenses = Expense::with('category')
            ->forUser($userId)
            ->byPeriod($startOfMonth, $endOfMonth)
            ->get()
            ->map(function ($item) {
                $item->item_type = 'single';
                $item->item_date = $item->date;
                return $item;
            });

        // 2. Ocorrências recorrentes do mês
        $occurrences = RecurringExpenseOccurrence::with('category')
            ->forUser($userId)
            ->byPeriod($startOfMonth, $endOfMonth)
            ->get()
            ->map(function ($item) {
                $item->item_type = 'recurring';
                $item->item_date = $item->due_date;
                return $item;
            });

        $monthlyItems = $singleExpenses->concat($occurrences);

        $monthlyTotal = $monthlyItems->sum(fn ($i) => (float) $i->amount);
        $monthlyPaid = $monthlyItems->where('status', 'paid')->sum(fn ($i) => (float) $i->amount);
        $monthlyPending = $monthlyItems->where('status', 'pending')->sum(fn ($i) => (float) $i->amount);
        $monthlyOverdue = $monthlyItems->where('status', 'overdue')->sum(fn ($i) => (float) $i->amount);
        $monthlyCount = $monthlyItems->count();

        // Últimos 5 lançamentos gerais do usuário
        $allSingle = Expense::with('category')->forUser($userId)->get()->map(function ($item) {
            $item->item_type = 'single';
            $item->item_date = $item->date;
            return $item;
        });

        $allOccurrences = RecurringExpenseOccurrence::with('category')->forUser($userId)->get()->map(function ($item) {
            $item->item_type = 'recurring';
            $item->item_date = $item->due_date;
            return $item;
        });

        $recentExpenses = $allSingle->concat($allOccurrences)
            ->sortByDesc(fn ($item) => $item->item_date->format('Y-m-d') . '_' . $item->id)
            ->take(5)
            ->values();

        // Gastos por categoria no mês atual
        $categoryBreakdown = $monthlyItems
            ->groupBy('category_id')
            ->map(function ($group) {
                return (object) [
                    'category_name' => $group->first()->category->name ?? 'Geral',
                    'total_amount' => $group->sum(fn ($i) => (float) $i->amount),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total_amount')
            ->values();

        return view('dashboard', compact(
            'monthlyTotal',
            'monthlyPaid',
            'monthlyPending',
            'monthlyOverdue',
            'monthlyCount',
            'recentExpenses',
            'categoryBreakdown'
        ));
    }
}
