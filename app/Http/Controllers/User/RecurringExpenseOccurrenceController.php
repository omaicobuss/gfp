<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\OccurrencePaymentRequest;
use App\Http\Requests\OccurrenceUpdateRequest;
use App\Models\Category;
use App\Models\RecurringExpenseOccurrence;
use App\Services\AttachmentService;
use App\Services\ExpenseStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecurringExpenseOccurrenceController extends Controller
{
    /**
     * Show form to record payment for an occurrence (FR-017).
     */
    public function payForm(RecurringExpenseOccurrence $occurrence): View
    {
        if ($occurrence->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a esta ocorrência.');
        }

        $occurrence->load('paymentReceipt');

        return view('occurrences.pay', compact('occurrence'));
    }

    /**
     * Record payment of an occurrence with actual amount and optional receipt (FR-017, FR-018, FR-020).
     */
    public function pay(OccurrencePaymentRequest $request, RecurringExpenseOccurrence $occurrence): RedirectResponse
    {
        if ($occurrence->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $occurrence->update([
            'actual_amount' => $request->input('actual_amount'),
            'paid_at' => $request->input('paid_at'),
            'status' => 'paid',
            'notes' => $request->filled('notes') ? $request->input('notes') : $occurrence->notes,
        ]);

        // Processar upload de comprovante de pagamento (FR-018)
        if ($request->hasFile('payment_receipt')) {
            AttachmentService::storeAttachment(
                $request->file('payment_receipt'),
                $occurrence,
                'payment_receipt',
                Auth::id()
            );
        }

        return redirect()->route('expenses.index')->with(
            'status',
            "Pagamento de '{$occurrence->description}' registrado com sucesso!"
        );
    }

    /**
     * Cancel/undo a payment of an occurrence.
     */
    public function unpay(RecurringExpenseOccurrence $occurrence): RedirectResponse
    {
        if ($occurrence->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $status = ExpenseStatusService::calculateStatus($occurrence->due_date, false, null);

        $occurrence->update([
            'actual_amount' => null,
            'paid_at' => null,
            'status' => $status,
        ]);

        return back()->with('status', "O pagamento de '{$occurrence->description}' foi desmarcado.");
    }

    /**
     * Show the form for editing the occurrence (FR-023).
     */
    public function edit(RecurringExpenseOccurrence $occurrence): View
    {
        if ($occurrence->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $categories = Category::forUser(Auth::id())->orderBy('name')->get();
        $occurrence->load('paymentReceipt');

        return view('occurrences.edit', compact('occurrence', 'categories'));
    }

    /**
     * Update the occurrence in storage (FR-023).
     */
    public function update(OccurrenceUpdateRequest $request, RecurringExpenseOccurrence $occurrence): RedirectResponse
    {
        if ($occurrence->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $isPaid = $request->filled('paid_at') || $request->filled('actual_amount');
        $status = ExpenseStatusService::calculateStatus(
            $request->input('due_date'),
            $isPaid,
            $request->input('paid_at')
        );

        $occurrence->update([
            'category_id' => $request->input('category_id'),
            'description' => trim($request->input('description')),
            'due_date' => $request->input('due_date'),
            'expected_amount' => $request->input('expected_amount'),
            'actual_amount' => $request->input('actual_amount'),
            'paid_at' => $request->input('paid_at'),
            'status' => $status,
            'notes' => $request->input('notes'),
        ]);

        if ($request->hasFile('payment_receipt')) {
            AttachmentService::storeAttachment(
                $request->file('payment_receipt'),
                $occurrence,
                'payment_receipt',
                Auth::id()
            );
        }

        return redirect()->route('expenses.index')->with('status', 'Ocorrência atualizada com sucesso!');
    }

    /**
     * Remove the occurrence from storage (FR-023).
     */
    public function destroy(RecurringExpenseOccurrence $occurrence): RedirectResponse
    {
        if ($occurrence->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $occurrence->delete();

        return redirect()->route('expenses.index')->with('status', 'Ocorrência excluída com sucesso!');
    }
}
