<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrar Pagamento — {{ $occurrence->description }}
            </h2>
            <a href="{{ url()->previous() ?: route('expenses.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 underline">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-100">

                <!-- Resumo da Ocorrência -->
                <div class="p-4 bg-indigo-50/60 rounded-xl border border-indigo-100 mb-6 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">Vencimento Previsto</div>
                        <div class="text-lg font-bold text-gray-900 mt-0.5">{{ $occurrence->due_date->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">Valor esperado: {{ $occurrence->formatted_expected_amount }}</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $occurrence->status_badge_class }}">
                            {{ $occurrence->status_label }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('occurrences.pay.store', $occurrence) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Valor Pago e Data do Pagamento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Valor Pago (R$) -->
                        <div>
                            <x-input-label for="actual_amount" value="Valor Efetivamente Pago (R$) *" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm font-semibold">
                                    R$
                                </span>
                                <input type="number" step="0.01" min="0.01" name="actual_amount" id="actual_amount" value="{{ old('actual_amount', $occurrence->actual_amount ?? $occurrence->expected_amount) }}" placeholder="0,00" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required autofocus />
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Pode ser diferente do valor esperado caso haja juros ou desconto.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('actual_amount')" />
                        </div>

                        <!-- Data do Pagamento -->
                        <div>
                            <x-input-label for="paid_at" value="Data do Pagamento *" />
                            <x-text-input id="paid_at" name="paid_at" type="date" class="mt-1 block w-full" :value="old('paid_at', $occurrence->paid_at ? $occurrence->paid_at->format('Y-m-d') : date('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('paid_at')" />
                        </div>
                    </div>

                    <!-- Comprovante de Pagamento (FR-018, FR-019) -->
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                        <x-input-label for="payment_receipt" value="🧾 Comprovante de Pagamento (Opcional)" />
                        <p class="text-xs text-gray-500">Envie o comprovante bancário ou recibo. Formatos: <strong>PDF, JPG ou PNG</strong> (máx. 10MB).</p>

                        @if ($occurrence->paymentReceipt)
                            <div class="flex items-center justify-between p-2.5 bg-white rounded-lg border border-gray-200 my-2">
                                <div class="text-xs font-medium text-gray-800 truncate">
                                    {{ $occurrence->paymentReceipt->original_name }} ({{ $occurrence->paymentReceipt->formatted_size }})
                                </div>
                                <a href="{{ route('attachments.download', $occurrence->paymentReceipt) }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                                    Baixar Atual
                                </a>
                            </div>
                        @endif

                        <input id="payment_receipt" name="payment_receipt" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                        <x-input-error class="mt-2" :messages="$errors->get('payment_receipt')" />
                    </div>

                    <!-- Observações -->
                    <div>
                        <x-input-label for="notes" value="Observações / Anotações (Opcional)" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Ex: Pago via Pix Banco X...">{{ old('notes', $occurrence->notes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                        <a href="{{ url()->previous() ?: route('expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </a>

                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 shadow-sm transition">
                            ✅ Confirmar Pagamento
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
