<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Models\RecurringExpenseOccurrence;
use App\Services\ExpenseStatusService;
use App\Services\RecurringExpenseService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Display a consolidated listing of user's expenses (non-recurring + recurring occurrences) (FR-029, FR-030).
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        // Garante que as ocorrências ativas estejam geradas para o período
        RecurringExpenseService::generateAllForUser($userId);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $categoryId = $request->filled('category_id') ? (int) $request->input('category_id') : null;
        $status = $request->input('status');
        $type = $request->input('type', 'all'); // 'all', 'single', 'recurring'
        $search = $request->input('search');

        $items = collect();

        // 1. Gastos Não Recorrentes (Avulsos)
        if ($type === 'all' || $type === 'single') {
            $singleExpenses = Expense::with(['category'])
                ->forUser($userId)
                ->byPeriod($startDate, $endDate)
                ->byCategory($categoryId)
                ->byStatus($status)
                ->search($search)
                ->get()
                ->map(function ($item) {
                    $item->item_type = 'single';
                    $item->item_date = $item->date;
                    $item->is_recurring = false;
                    return $item;
                });

            $items = $items->concat($singleExpenses);
        }

        // 2. Ocorrências de Gastos Recorrentes
        if ($type === 'all' || $type === 'recurring') {
            $occurrences = RecurringExpenseOccurrence::with(['category', 'recurringExpense.billingDocument', 'paymentReceipt'])
                ->forUser($userId)
                ->byPeriod($startDate, $endDate)
                ->byCategory($categoryId)
                ->byStatus($status)
                ->search($search)
                ->get()
                ->map(function ($item) {
                    $item->item_type = 'recurring';
                    $item->item_date = $item->due_date;
                    $item->is_recurring = true;
                    return $item;
                });

            $items = $items->concat($occurrences);
        }

        // Ordenação por data decrescente
        $sorted = $items->sortByDesc(function ($item) {
            return $item->item_date->format('Y-m-d') . '_' . $item->id;
        })->values();

        // Totais consolidados
        $totalAmount = $sorted->sum(fn ($i) => (float) $i->amount);
        $paidAmount = $sorted->where('status', 'paid')->sum(fn ($i) => (float) $i->amount);
        $pendingAmount = $sorted->where('status', 'pending')->sum(fn ($i) => (float) $i->amount);
        $overdueAmount = $sorted->where('status', 'overdue')->sum(fn ($i) => (float) $i->amount);

        // Paginação manual do Collection consolidado
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $expenses = new LengthAwarePaginator(
            $currentItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $categories = Category::forUser($userId)->orderBy('name')->get();

        return view('expenses.index', compact(
            'expenses',
            'categories',
            'totalAmount',
            'paidAmount',
            'pendingAmount',
            'overdueAmount'
        ));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(): View
    {
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();

        return view('expenses.create', compact('categories'));
    }

    /**
     * Store a newly created expense in storage (FR-014, FR-020).
     */
    public function store(ExpenseRequest $request): RedirectResponse
    {
        $isPaid = $request->boolean('is_paid');
        $paidAt = $isPaid ? ($request->input('date') ?? now()) : null;

        $status = ExpenseStatusService::calculateStatus(
            $request->input('date'),
            $isPaid,
            $paidAt
        );

        Expense::create([
            'user_id' => Auth::id(),
            'category_id' => $request->input('category_id'),
            'description' => trim($request->input('description')),
            'amount' => $request->input('amount'),
            'date' => $request->input('date'),
            'status' => $status,
            'paid_at' => $paidAt,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('expenses.index')->with('status', 'Gasto cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense): View
    {
        // Garante isolamento total de dados entre usuários (FR-024)
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este gasto.');
        }

        $categories = Category::forUser(Auth::id())->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified expense in storage (FR-021).
     */
    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        // Garante isolamento total de dados entre usuários (FR-024)
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este gasto.');
        }

        $isPaid = $request->boolean('is_paid');
        $paidAt = $isPaid ? ($expense->paid_at ?? $request->input('date')) : null;

        $status = ExpenseStatusService::calculateStatus(
            $request->input('date'),
            $isPaid,
            $paidAt
        );

        $expense->update([
            'category_id' => $request->input('category_id'),
            'description' => trim($request->input('description')),
            'amount' => $request->input('amount'),
            'date' => $request->input('date'),
            'status' => $status,
            'paid_at' => $paidAt,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('expenses.index')->with('status', 'Gasto atualizado com sucesso!');
    }

    /**
     * Remove the specified expense from storage (FR-021).
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        // Garante isolamento total de dados entre usuários (FR-024)
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este gasto.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Gasto excluído com sucesso!');
    }
}
