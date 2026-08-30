<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Ocorrência — {{ $occurrence->description }}
            </h2>
            <a href="{{ url()->previous() ?: route('expenses.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 underline">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-100">
                <form action="{{ route('occurrences.update', $occurrence) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Descrição -->
                    <div>
                        <x-input-label for="description" value="Descrição da Ocorrência *" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $occurrence->description)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <!-- Categoria e Data de Vencimento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Categoria -->
                        <div>
                            <x-input-label for="category_id" value="Categoria *" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $occurrence->category_id) == $category->id ? 'selected' : '' }}>
                                        🏷️ {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        <!-- Data de Vencimento -->
                        <div>
                            <x-input-label for="due_date" value="Data de Vencimento *" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', $occurrence->due_date->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
                        </div>
                    </div>

                    <!-- Valor Esperado e Valor Pago -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Valor Esperado -->
                        <div>
                            <x-input-label for="expected_amount" value="Valor Esperado (R$) *" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm font-semibold">
                                    R$
                                </span>
                                <input type="number" step="0.01" min="0.01" name="expected_amount" id="expected_amount" value="{{ old('expected_amount', $occurrence->expected_amount) }}" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('expected_amount')" />
                        </div>

                        <!-- Valor Pago (se pago) -->
                        <div>
                            <x-input-label for="actual_amount" value="Valor Efetivamente Pago (R$)" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm font-semibold">
                                    R$
                                </span>
                                <input type="number" step="0.01" min="0.01" name="actual_amount" id="actual_amount" value="{{ old('actual_amount', $occurrence->actual_amount) }}" placeholder="Opcional se pendente" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('actual_amount')" />
                        </div>
                    </div>

                    <!-- Data do Pagamento -->
                    <div>
                        <x-input-label for="paid_at" value="Data do Pagamento (Opcional)" />
                        <x-text-input id="paid_at" name="paid_at" type="date" class="mt-1 block w-full" :value="old('paid_at', $occurrence->paid_at ? $occurrence->paid_at->format('Y-m-d') : '')" />
                        <p class="text-xs text-gray-500 mt-1">Deixe em branco se a conta ainda não tiver sido paga.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('paid_at')" />
                    </div>

                    <!-- Comprovante de Pagamento -->
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                        <x-input-label for="payment_receipt" value="🧾 Comprovante de Pagamento" />
                        @if ($occurrence->paymentReceipt)
                            <div class="flex items-center justify-between p-2.5 bg-white rounded-lg border border-gray-200 my-2">
                                <div class="text-xs font-medium text-gray-800 truncate">
                                    {{ $occurrence->paymentReceipt->original_name }} ({{ $occurrence->paymentReceipt->formatted_size }})
                                </div>
                                <a href="{{ route('attachments.download', $occurrence->paymentReceipt) }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                                    Baixar Atual
                                </a>
                            </div>
                            <p class="text-xs text-gray-500">Para substituir o arquivo atual, envie um novo comprovante:</p>
                        @endif

                        <input id="payment_receipt" name="payment_receipt" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                        <x-input-error class="mt-2" :messages="$errors->get('payment_receipt')" />
                    </div>

                    <!-- Observações -->
                    <div>
                        <x-input-label for="notes" value="Observações / Anotações" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('notes', $occurrence->notes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                        <a href="{{ url()->previous() ?: route('expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </a>

                        <x-primary-button>
                            Salvar Alterações
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
