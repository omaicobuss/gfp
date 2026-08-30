<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Services\ExpenseStatusService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the user's expenses with filters and summaries (FR-029, FR-030).
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $query = Expense::with('category')
            ->forUser($userId)
            ->byPeriod($request->input('start_date'), $request->input('end_date'))
            ->byCategory($request->filled('category_id') ? (int) $request->input('category_id') : null)
            ->byStatus($request->input('status'))
            ->search($request->input('search'))
            ->orderByDesc('date')
            ->orderByDesc('id');

        // Totais consolidados para os cards de resumo
        $totalAmount = (clone $query)->sum('amount');
        $paidAmount = (clone $query)->where('status', 'paid')->sum('amount');
        $pendingAmount = (clone $query)->where('status', 'pending')->sum('amount');
        $overdueAmount = (clone $query)->where('status', 'overdue')->sum('amount');

        $expenses = $query->paginate(15)->withQueryString();

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
