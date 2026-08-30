<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecurringExpenseRequest;
use App\Models\Category;
use App\Models\RecurringExpense;
use App\Services\AttachmentService;
use App\Services\RecurringExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecurringExpenseController extends Controller
{
    /**
     * Display a listing of recurring expenses for the user (FR-015, FR-029).
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $query = RecurringExpense::with(['category', 'billingDocument'])
            ->forUser($userId)
            ->byCategory($request->filled('category_id') ? (int) $request->input('category_id') : null)
            ->byFrequency($request->input('frequency'))
            ->search($request->input('search'))
            ->orderBy('due_date');

        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $recurringExpenses = $query->paginate(15)->withQueryString();

        $categories = Category::forUser($userId)->orderBy('name')->get();

        // Totais para cards informativos
        $totalMonthlyExpected = RecurringExpense::forUser($userId)
            ->active()
            ->get()
            ->sum(function (RecurringExpense $item) {
                return match ($item->frequency) {
                    'weekly' => $item->expected_amount * 4,
                    'monthly' => $item->expected_amount,
                    'yearly' => $item->expected_amount / 12,
                    'custom' => $item->expected_amount * (30 / ($item->frequency_days ?: 30)),
                    default => $item->expected_amount,
                };
            });

        $activeCount = RecurringExpense::forUser($userId)->active()->count();

        return view('recurring-expenses.index', compact(
            'recurringExpenses',
            'categories',
            'totalMonthlyExpected',
            'activeCount'
        ));
    }

    /**
     * Show the form for creating a new recurring expense.
     */
    public function create(): View
    {
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();

        return view('recurring-expenses.create', compact('categories'));
    }

    /**
     * Store a newly created recurring expense in storage (FR-015, FR-016).
     */
    public function store(RecurringExpenseRequest $request): RedirectResponse
    {
        $recurringExpense = RecurringExpense::create([
            'user_id' => Auth::id(),
            'category_id' => $request->input('category_id'),
            'description' => trim($request->input('description')),
            'expected_amount' => $request->input('expected_amount'),
            'frequency' => $request->input('frequency'),
            'frequency_days' => $request->input('frequency') === 'custom' ? $request->input('frequency_days') : null,
            'due_day' => $request->input('frequency') === 'monthly' ? $request->input('due_day') : null,
            'due_date' => $request->input('due_date'),
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->input('notes'),
        ]);

        // Processar upload de Documento de Cobrança se fornecido (FR-016, FR-019)
        if ($request->hasFile('billing_document')) {
            AttachmentService::storeAttachment(
                $request->file('billing_document'),
                $recurringExpense,
                'billing_document',
                Auth::id()
            );
        }

        // Gera automaticamente as primeiras ocorrências deste gasto recorrente (FR-015)
        RecurringExpenseService::generateOccurrences($recurringExpense);

        return redirect()->route('recurring-expenses.index')->with('status', 'Gasto recorrente cadastrado com sucesso!');
    }

    /**
     * Display the specified recurring expense details.
     */
    public function show(RecurringExpense $recurringExpense): View
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este gasto recorrente.');
        }

        // Carrega ocorrências e documentos
        $recurringExpense->load([
            'category',
            'billingDocument',
            'occurrences' => function ($q) {
                $q->with('paymentReceipt')->orderByDesc('due_date');
            },
        ]);

        return view('recurring-expenses.show', compact('recurringExpense'));
    }

    /**
     * Show the form for editing the specified recurring expense.
     */
    public function edit(RecurringExpense $recurringExpense): View
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este gasto recorrente.');
        }

        $categories = Category::forUser(Auth::id())->orderBy('name')->get();
        $recurringExpense->load('billingDocument');

        return view('recurring-expenses.edit', compact('recurringExpense', 'categories'));
    }

    /**
     * Update the specified recurring expense in storage (FR-022).
     */
    public function update(RecurringExpenseRequest $request, RecurringExpense $recurringExpense): RedirectResponse
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este gasto recorrente.');
        }

        $recurringExpense->update([
            'category_id' => $request->input('category_id'),
            'description' => trim($request->input('description')),
            'expected_amount' => $request->input('expected_amount'),
            'frequency' => $request->input('frequency'),
            'frequency_days' => $request->input('frequency') === 'custom' ? $request->input('frequency_days') : null,
            'due_day' => $request->input('frequency') === 'monthly' ? $request->input('due_day') : null,
            'due_date' => $request->input('due_date'),
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->input('notes'),
        ]);

        // Processar substituição do documento de cobrança se novo arquivo for enviado
        if ($request->hasFile('billing_document')) {
            AttachmentService::storeAttachment(
                $request->file('billing_document'),
                $recurringExpense,
                'billing_document',
                Auth::id()
            );
        }

        return redirect()->route('recurring-expenses.index')->with('status', 'Gasto recorrente atualizado com sucesso!');
    }

    /**
     * Remove the specified recurring expense from storage (FR-022).
     */
    public function destroy(RecurringExpense $recurringExpense): RedirectResponse
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este gasto recorrente.');
        }

        $recurringExpense->delete();

        return redirect()->route('recurring-expenses.index')->with('status', 'Gasto recorrente excluído com sucesso!');
    }

    /**
     * Toggle active state of recurring expense.
     */
    public function toggleActive(RecurringExpense $recurringExpense): RedirectResponse
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $recurringExpense->update([
            'is_active' => ! $recurringExpense->is_active,
        ]);

        $status = $recurringExpense->is_active ? 'reativado' : 'pausado';

        return redirect()->route('recurring-expenses.index')->with(
            'status',
            "O gasto recorrente '{$recurringExpense->description}' foi {$status}."
        );
    }

    /**
     * Manually trigger generation of future occurrences.
     */
    public function generateOccurrences(RecurringExpense $recurringExpense): RedirectResponse
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $created = RecurringExpenseService::generateOccurrences($recurringExpense, now()->addMonths(6));

        $msg = $created > 0
            ? "{$created} novas ocorrências de vencimento foram geradas."
            : 'Todas as ocorrências do período já estavam geradas.';

        return back()->with('status', $msg);
    }
}
