<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard with monthly overview and recent activity.
     */
    public function index(): View
    {
        $userId = Auth::id();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // Gastos do mês atual
        $monthlyQuery = Expense::with('category')
            ->forUser($userId)
            ->byPeriod($startOfMonth, $endOfMonth);

        $monthlyTotal = (clone $monthlyQuery)->sum('amount');
        $monthlyPaid = (clone $monthlyQuery)->where('status', 'paid')->sum('amount');
        $monthlyPending = (clone $monthlyQuery)->where('status', 'pending')->sum('amount');
        $monthlyOverdue = (clone $monthlyQuery)->where('status', 'overdue')->sum('amount');
        $monthlyCount = (clone $monthlyQuery)->count();

        // Últimos 5 gastos cadastrados
        $recentExpenses = Expense::with('category')
            ->forUser($userId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Gastos por categoria no mês atual
        $categoryBreakdown = Expense::forUser($userId)
            ->byPeriod($startOfMonth, $endOfMonth)
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(expenses.amount) as total_amount, COUNT(expenses.id) as count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_amount')
            ->get();

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
